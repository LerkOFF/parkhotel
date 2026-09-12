<?php
declare(strict_types=1);

session_start();

const ROOT_DIR = __DIR__ . '/..';
const DB_FILE = ROOT_DIR . '/var/parkhotel.sqlite';

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

function notify_request(array $request): void
{
    $email = env_value('PARKHOTEL_NOTIFY_EMAIL');
    if (!$email) {
        return;
    }

    $subject = 'Новая заявка: ' . $request['type'];
    $body = "Имя: {$request['name']}\nТелефон: {$request['phone']}\nEmail: {$request['email']}\nСообщение: {$request['message']}";
    @mail($email, $subject, $body, 'Content-Type: text/plain; charset=UTF-8');
}

db();
