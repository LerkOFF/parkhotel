<?php
declare(strict_types=1);

session_start();
date_default_timezone_set('Europe/Moscow');

const ROOT_DIR = __DIR__ . '/..';
const DB_FILE = ROOT_DIR . '/var/parkhotel.sqlite';
const UPLOAD_DIR = ROOT_DIR . '/uploads';
const MAX_UPLOAD_BYTES = 10 * 1024 * 1024;

function env_value(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (!is_dir(dirname(DB_FILE))) {
        mkdir(dirname(DB_FILE), 0775, true);
    }

    $pdo = new PDO('sqlite:' . DB_FILE, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('CREATE TABLE IF NOT EXISTS page_content (
        slug TEXT PRIMARY KEY,
        title TEXT NOT NULL,
        description TEXT NOT NULL,
        h1 TEXT NOT NULL,
        intro TEXT NOT NULL,
        body TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        type TEXT NOT NULL,
        name TEXT NOT NULL,
        phone TEXT NOT NULL,
        email TEXT NOT NULL DEFAULT "",
        message TEXT NOT NULL DEFAULT "",
        status TEXT NOT NULL DEFAULT "new",
        created_at TEXT NOT NULL
    )');
    $pdo->exec('CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )');
    $requestColumns = array_column($pdo->query('PRAGMA table_info(requests)')->fetchAll(), 'name');
    if (!in_array('notify_status', $requestColumns, true)) {
        $pdo->exec('ALTER TABLE requests ADD COLUMN notify_status TEXT NOT NULL DEFAULT ""');
    }
    $pdo->exec("UPDATE requests SET status = 'confirmed' WHERE status IN ('working', 'done')");
    $pdo->exec("UPDATE requests SET status = 'cancelled' WHERE status = 'canceled'");

    seed_pages($pdo);
    migrate_seed_content($pdo);
    return $pdo;
}

/**
 * Подтягивает страницы к текущему тексту из app/pages.php, если админ их не правил:
 * тогда в базе лежит один из прежних исходных текстов (сравнение по sha1). Правки из админки не затирает.
 */
function migrate_seed_content(PDO $pdo): void
{
    $previousSeeds = [
        'nomera' => [
            '885715dab709d4ff31d76558286cd18a54a5add2', // заглушка первого этапа
            'fe1262991610c67259db925d95247367501f2333', // 13.10.2026: 22 номера, King Size, завтраки
            '87e0284993b03e8cffe5f58297dfccccc9b54a0f', // 21.10.2026: ненадолго убирали текст завтраков, вернули
        ],
        'kontakty' => [
            '57cfa3a4987d8497bfbfe8a3ca9b9bacd22e3f42', // заглушка «подтвердить у модератора»
        ],
        'slavyanka' => [
            '5f9604f4292199a68341d6bef439818213484d54', // заглушка с ценой «по ТЗ»
        ],
    ];
    $seeds = pages_seed();
    $select = $pdo->prepare('SELECT body FROM page_content WHERE slug = ?');
    $update = $pdo->prepare('UPDATE page_content SET title = ?, description = ?, h1 = ?, intro = ?, body = ?, updated_at = ? WHERE slug = ?');
    foreach ($previousSeeds as $slug => $hashes) {
        $select->execute([$slug]);
        $current = $select->fetchColumn();
        if (!is_string($current) || !in_array(sha1($current), $hashes, true)) {
            continue;
        }
        $page = $seeds[$slug];
        $update->execute([$page['title'], $page['description'], $page['h1'], $page['intro'], $page['body'], date(DATE_ATOM), $slug]);
    }
    // 21.10.2026: почту везде пишем строчными, в уже заведённых страницах заменяем адрес точечно, остальной текст не трогаем
    $pdo->exec("UPDATE page_content SET body = REPLACE(body, 'ParkHotelVP@yandex.ru', 'parkhotelvp@yandex.ru') WHERE body GLOB '*ParkHotelVP@yandex.ru*'");
}

function pages_seed(): array
{
    return require __DIR__ . '/pages.php';
}

function seed_pages(PDO $pdo): void
{
    $stmt = $pdo->prepare('INSERT OR IGNORE INTO page_content (slug, title, description, h1, intro, body, updated_at) VALUES (:slug, :title, :description, :h1, :intro, :body, :updated_at)');
    foreach (pages_seed() as $slug => $page) {
        $stmt->execute([
            'slug' => $slug,
            'title' => $page['title'],
            'description' => $page['description'],
            'h1' => $page['h1'],
            'intro' => $page['intro'],
            'body' => $page['body'],
            'updated_at' => date(DATE_ATOM),
        ]);
    }
}

function page_by_slug(string $slug): ?array
{
    $stmt = db()->prepare('SELECT * FROM page_content WHERE slug = ?');
    $stmt->execute([$slug]);
    $page = $stmt->fetch();
    return $page ?: null;
}

function all_pages(): array
{
    $rows = db()->query('SELECT * FROM page_content')->fetchAll();
    $seedOrder = array_keys(pages_seed());
    usort($rows, fn(array $a, array $b): int => array_search($a['slug'], $seedOrder, true) <=> array_search($b['slug'], $seedOrder, true));
    return $rows;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function head_icons(): string
{
    return '<link rel="icon" href="/favicon.ico" sizes="any">'
        . '<link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png">'
        . '<link rel="apple-touch-icon" href="/apple-touch-icon.png">';
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) $token)) {
        http_response_code(419);
        throw new RuntimeException('Сессия формы устарела. Обновите страницу.');
    }
}

function is_admin(): bool
{
    return !empty($_SESSION['admin']);
}

function verify_admin_password(string $password): bool
{
    $hashFile = env_value('PARKHOTEL_ADMIN_PASSWORD_HASH_FILE');
    if (!$hashFile) {
        $localHash = ROOT_DIR . '/var/admin_password_hash';
        if (is_readable($localHash)) {
            $hashFile = $localHash;
        }
    }
    if ($hashFile && is_readable($hashFile)) {
        $hash = trim((string) file_get_contents($hashFile));
        return $hash !== '' && password_verify($password, $hash);
    }

    $hash = env_value('PARKHOTEL_ADMIN_PASSWORD_HASH');
    if ($hash) {
        return password_verify($password, $hash);
    }

    return hash_equals(env_value('PARKHOTEL_ADMIN_PASSWORD', 'admin123') ?? 'admin123', $password);
}

function sanitize_content_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $allowedTags = ['p', 'br', 'h2', 'h3', 'strong', 'b', 'em', 'i', 'ul', 'ol', 'li', 'a', 'img', 'blockquote', 'figure', 'figcaption'];
    $document = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="UTF-8"><div id="content-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $root = $document->getElementById('content-root');
    if (!$root) {
        return '';
    }

    $walker = function (DOMNode $node) use (&$walker, $allowedTags): void {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMComment) {
                $node->removeChild($child);
                continue;
            }
            if (!$child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if (!in_array($tag, $allowedTags, true)) {
                if (in_array($tag, ['script', 'style', 'template', 'iframe', 'object', 'embed'], true)) {
                    $node->removeChild($child);
                    continue;
                }
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attribute) {
                $allowed = ($tag === 'a' && in_array($attribute->name, ['href', 'target', 'rel'], true))
                    || ($tag === 'img' && in_array($attribute->name, ['src', 'alt'], true));
                if (!$allowed) {
                    $child->removeAttribute($attribute->name);
                }
            }

            if ($tag === 'a') {
                $href = trim($child->getAttribute('href'));
                if (!preg_match('~^(?:https?://|mailto:|tel:|/)~i', $href)) {
                    $child->removeAttribute('href');
                }
                if ($child->getAttribute('target') === '_blank') {
                    $child->setAttribute('rel', 'noopener noreferrer');
                } else {
                    $child->removeAttribute('target');
                    $child->removeAttribute('rel');
                }
            }

            if ($tag === 'img') {
                $src = trim($child->getAttribute('src'));
                if (!preg_match('~^(?:https?://|/uploads/)~i', $src)) {
                    $node->removeChild($child);
                    continue;
                }
            }
            $walker($child);
        }
    };
    $walker($root);

    $clean = '';
    foreach ($root->childNodes as $child) {
        $clean .= $document->saveHTML($child);
    }
    return trim($clean);
}

function save_uploaded_file(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Не удалось загрузить файл.');
    }
    if (($file['size'] ?? 0) < 1 || $file['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('Размер файла должен быть не больше 10 МБ.');
    }

    $allowed = [
        'image/jpeg' => ['jpg', true], 'image/png' => ['png', true],
        'image/webp' => ['webp', true], 'image/gif' => ['gif', true],
        'application/pdf' => ['pdf', false],
        'application/msword' => ['doc', false],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx', false],
        'application/vnd.ms-excel' => ['xls', false],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx', false],
    ];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Допустимы изображения, PDF, Word и Excel.');
    }

    if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0775, true) && !is_dir(UPLOAD_DIR)) {
        throw new RuntimeException('Папка загрузок недоступна.');
    }
    [$extension, $isImage] = $allowed[$mime];
    $filename = date('Ymd') . '-' . bin2hex(random_bytes(12)) . '.' . $extension;
    if (!move_uploaded_file((string) $file['tmp_name'], UPLOAD_DIR . '/' . $filename)) {
        throw new RuntimeException('Не удалось сохранить файл.');
    }

    return [
        'url' => '/uploads/' . $filename,
        'name' => trim((string) ($file['name'] ?? '')) ?: $filename,
        'image' => $isImage,
    ];
}

function setting_get(string $key, string $default = ''): string
{
    $stmt = db()->prepare('SELECT value FROM settings WHERE key = ?');
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    return $value === false ? $default : (string) $value;
}

function setting_set(string $key, string $value): void
{
    $stmt = db()->prepare('INSERT INTO settings (key, value, updated_at) VALUES (?, ?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value, updated_at = excluded.updated_at');
    $stmt->execute([$key, $value, date(DATE_ATOM)]);
}

/** Разбирает строку адресов через запятую или точку с запятой. Возвращает [корректные, некорректные]. */
function parse_email_list(string $raw): array
{
    $valid = [];
    $invalid = [];
    foreach (preg_split('~[\s,;]+~u', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $item) {
        if (filter_var($item, FILTER_VALIDATE_EMAIL)) {
            $valid[strtolower($item)] ??= $item;
        } else {
            $invalid[] = $item;
        }
    }
    return [array_values($valid), $invalid];
}

/** Адреса получателей заявок: настройка из админки, иначе переменная окружения. */
function notify_emails(): array
{
    $raw = setting_get('notify_email');
    if (trim($raw) === '') {
        $raw = (string) env_value('PARKHOTEL_NOTIFY_EMAIL', '');
    }
    return parse_email_list($raw)[0];
}

function mail_from_address(): string
{
    return env_value('PARKHOTEL_MAIL_FROM') ?: 'noreply@parkhotelvp.ru';
}

function mail_log(string $line): void
{
    $file = ROOT_DIR . '/var/mail.log';
    @file_put_contents($file, date(DATE_ATOM) . ' ' . $line . "\n", FILE_APPEND | LOCK_EX);
}

/** Отправляет текстовое письмо через mail(). Возвращает true, если почтовая служба хоста приняла письмо. */
function send_mail(string $to, string $subject, string $body): bool
{
    $from = mail_from_address();
    $fromHeader = '=?UTF-8?B?' . base64_encode('Парк-Отель') . '?= <' . $from . '>';
    $headers = [
        'From: ' . $fromHeader,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: base64',
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . substr(strrchr($from, '@'), 1) . '>',
        'X-Auto-Response-Suppress: All',
    ];
    $encodedSubject = '=?UTF-8?B?' . base64_encode(preg_replace('~[\r\n]+~', ' ', $subject)) . '?=';
    $message = chunk_split(base64_encode($body));

    $sent = @mail($to, $encodedSubject, $message, implode("\r\n", $headers), '-f' . $from);
    if (!$sent) {
        $sent = @mail($to, $encodedSubject, $message, implode("\r\n", $headers));
    }
    mail_log(($sent ? 'ok   ' : 'FAIL ') . $to . ' | ' . $subject);
    return $sent;
}

/** Рассылает заявку получателям. Возвращает статус для списка заявок: sent, failed или none (адрес не задан). */
function notify_request(array $request): string
{
    $emails = notify_emails();
    if (!$emails) {
        mail_log('skip  адрес получателя заявок не задан | ' . $request['type']);
        return 'none';
    }

    $subject = 'Новая заявка: ' . $request['type'];
    $body = "Новая заявка с сайта parkhotelvp.ru\n\n"
        . "Раздел: {$request['type']}\n"
        . "Имя: {$request['name']}\n"
        . "Телефон: {$request['phone']}\n"
        . 'Время: ' . date('d.m.Y H:i') . "\n\n"
        . "Все заявки и статусы: https://parkhotelvp.ru/admin\n";

    $sent = 0;
    foreach ($emails as $email) {
        $sent += send_mail($email, $subject, $body) ? 1 : 0;
    }
    return $sent > 0 ? 'sent' : 'failed';
}

db();
