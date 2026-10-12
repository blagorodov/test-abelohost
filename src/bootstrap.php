<?php

use Smarty\Smarty;

require dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/Category.php';
require_once __DIR__ . '/Post.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $env = parse_ini_file(dirname(__DIR__) . '/.env', false, INI_SCANNER_RAW)
        ?: throw new RuntimeException('.env not found');

    $pdo = new PDO(
        "mysql:host={$env['MYSQL_HOST']};port={$env['MYSQL_PORT']};dbname={$env['MYSQL_DATABASE']};charset=utf8mb4",
        $env['MYSQL_USER'],
        $env['MYSQL_PASSWORD'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    );

    return $pdo;
}

function render(string $template, int $status = 200, array $vars = []): void
{
    http_response_code($status);
    $root = dirname(__DIR__);
    $smarty = new Smarty();
    $smarty->setTemplateDir($root . '/templates');
    $smarty->setCompileDir($root . '/templates_c');
    $smarty->setCacheDir($root . '/cache');
    $smarty->assign($vars);
    $smarty->display($template);
}
