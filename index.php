<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/', '/');

if ($path === 'api/request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        verify_csrf();
        $name = trim((string) ($_POST['name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));
        $type = trim((string) ($_POST['type'] ?? 'Обратная связь'));
        if (mb_strlen($name) < 2 || mb_strlen($phone) < 6) {
            throw new InvalidArgumentException('Укажите имя и телефон.');
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Проверьте адрес электронной почты.');
        }
        $stmt = db()->prepare('INSERT INTO requests (type, name, phone, email, message, created_at) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$type, $name, $phone, $email, $message, date(DATE_ATOM)]);
        notify_request(compact('type', 'name', 'phone', 'email', 'message'));
        echo json_encode(['ok' => true, 'message' => 'Заявка принята. Администратор свяжется с вами.'], JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($path === 'admin/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (verify_admin_password((string) ($_POST['password'] ?? ''))) {
        $_SESSION['admin'] = true;
        header('Location: /admin');
        exit;
    }
    $loginError = 'Неверный пароль.';
}

if ($path === 'admin/logout') {
    session_destroy();
    header('Location: /');
    exit;
}

if (str_starts_with($path, 'admin')) {
    if (!is_admin()) {
        require __DIR__ . '/app/view-admin-login.php';
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        if (isset($_POST['save_page'])) {
            $stmt = db()->prepare('UPDATE page_content SET title=?, description=?, h1=?, intro=?, body=?, updated_at=? WHERE slug=?');
            $stmt->execute([
                trim((string) $_POST['title']), trim((string) $_POST['description']),
                trim((string) $_POST['h1']), trim((string) $_POST['intro']),
                trim((string) $_POST['body']), date(DATE_ATOM), (string) $_POST['slug'],
            ]);
        }
        if (isset($_POST['request_status'])) {
            $allowed = ['new', 'working', 'done'];
            $status = in_array($_POST['status'] ?? '', $allowed, true) ? $_POST['status'] : 'new';
            $stmt = db()->prepare('UPDATE requests SET status=? WHERE id=?');
            $stmt->execute([$status, (int) $_POST['request_id']]);
        }
        header('Location: /admin?' . http_build_query(['page' => $_GET['page'] ?? 'requests', 'saved' => 1]));
        exit;
    }

    $adminPage = (string) ($_GET['page'] ?? 'requests');
    $requests = db()->query('SELECT * FROM requests ORDER BY id DESC')->fetchAll();
    require __DIR__ . '/app/view-admin.php';
    exit;
}

$routeMap = [
    '' => 'home', 'nomera' => 'nomera', 'basseyn' => 'basseyn', 'sauna-banya' => 'sauna-banya',
    'slavyanka' => 'slavyanka', 'pominalnye-obedy' => 'pominalnye-obedy',
    'banketnyy-zal' => 'banketnyy-zal', 'arenda' => 'arenda', 'o-nas' => 'o-nas',
    'ryadom-s-otelem' => 'ryadom-s-otelem', 'kontakty' => 'kontakty',
];

if (!array_key_exists($path, $routeMap)) {
    http_response_code(404);
    $page = ['title' => 'Страница не найдена', 'description' => '', 'h1' => 'Страница не найдена', 'intro' => 'Вернитесь на главную страницу.', 'body' => ''];
    $slug = '404';
} else {
    $slug = $routeMap[$path];
    $page = page_by_slug($slug);
}

require __DIR__ . '/app/view-site.php';
