<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$file = __DIR__ . $path;

if (preg_match('#^/(app|var|docs)(/|$)#', $path)) {
    http_response_code(404);
    exit('Not found');
}

if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
