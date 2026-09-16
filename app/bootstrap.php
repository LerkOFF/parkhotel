<?php
declare(strict_types=1);

session_start();

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
    $pdo->exec("UPDATE requests SET status = 'confirmed' WHERE status IN ('working', 'done')");
    $pdo->exec("UPDATE requests SET status = 'cancelled' WHERE status = 'canceled'");

    seed_pages($pdo);
    return $pdo;
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

function notify_request(array $request): void
{
    $email = env_value('PARKHOTEL_NOTIFY_EMAIL');
    if (!$email) {
        return;
    }

    $subject = 'Новая заявка: ' . $request['type'];
    $body = "Имя: {$request['name']}\nТелефон: {$request['phone']}";
    @mail($email, $subject, $body, 'Content-Type: text/plain; charset=UTF-8');
}

db();
