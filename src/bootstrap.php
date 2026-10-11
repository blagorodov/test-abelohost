<?php

require dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/renderer.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = require dirname(__DIR__) . '/config.php';
    require_once __DIR__ . '/Db.php';
    $pdo = new Db($config)->pdo();

    return $pdo;
}
