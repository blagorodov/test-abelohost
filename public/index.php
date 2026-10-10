<?php

use Smarty\Smarty;

require dirname(__DIR__) . '/vendor/autoload.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($path === null || $path === '') {
    $path = '/';
}

$root = dirname(__DIR__);

try {
    if ($path === '/' || $path === '/index.php') {
        $config = require $root . '/config.php';
        require_once $root . '/src/Db.php';
        new Db($config)->pdo()->query('SELECT 1');
        render('index.tpl');
        return;
    }

    render('404.tpl', 404);
} catch (Throwable) {
    render('error.tpl', 500);
}

function render(string $template, int $status = 200): void
{
    http_response_code($status);
    $root = dirname(__DIR__);
    $smarty = new Smarty();
    $smarty->setTemplateDir($root . '/templates');
    $smarty->setCompileDir($root . '/templates_c');
    $smarty->setCacheDir($root . '/cache');
    $smarty->display($template);
}
