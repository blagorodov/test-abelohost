<?php

require dirname(__DIR__) . '/src/bootstrap.php';
require_once dirname(__DIR__) . '/src/Post.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($path === null || $path === '') {
    $path = '/';
}

try {
    if ($path === '/' || $path === '/index.php') {
        render('home.tpl', 200, ['categories' => new Post(db())->latestByCategory()]);
        return;
    }

    render('404.tpl', 404);
} catch (PDOException) {
    render('500.tpl', 500, ['error' => 'База недоступна']);
} catch (Throwable $throwable) {
    render('500.tpl', 500, ['error' => $throwable->getMessage()]);
}
