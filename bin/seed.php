<?php

$root = dirname(__DIR__);
$config = require $root . '/config.php';
require_once $root . '/src/Db.php';

$categories = readJson($root . '/data/categories.json');
$posts = readJson($root . '/data/posts.json');

foreach ($posts as $post) {
    $source = $root . '/data/images/' . basename($post['image']);
    if (!is_file($source)) {
        fwrite(STDERR, "Нет файла {$source}\n");
        exit(1);
    }
}

$pdo = new Db($config)->pdo();
$pdo->exec('DELETE FROM post_category');
$pdo->exec('DELETE FROM posts');
$pdo->exec('DELETE FROM categories');

$destDir = $root . '/public/images';
if (!is_dir($destDir) && !mkdir($destDir, 0775, true) && !is_dir($destDir)) {
    fwrite(STDERR, "Не удалось создать {$destDir}\n");
    exit(1);
}

foreach ($posts as $post) {
    $name = basename($post['image']);
    if (!copy($root . '/data/images/' . $name, $destDir . '/' . $name)) {
        fwrite(STDERR, "Не удалось скопировать {$name}\n");
        exit(1);
    }
}

$now = time();
$from = strtotime('-1 month', $now);
$insertCategory = $pdo->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
$insertPost = $pdo->prepare(
    'INSERT INTO posts (image, title, description, body, views, published_at) VALUES (?, ?, ?, ?, ?, ?)'
);
$insertLink = $pdo->prepare('INSERT INTO post_category (post_id, category_id) VALUES (?, ?)');

$ids = [];
foreach ($categories as $category) {
    $insertCategory->execute([$category['name'], $category['description']]);
    $ids[$category['name']] = (int) $pdo->lastInsertId();
}

foreach ($posts as $post) {
    $insertPost->execute([
        $post['image'],
        $post['title'],
        $post['description'],
        $post['body'],
        random_int(0, 50),
        date('Y-m-d H:i:s', random_int($from, $now)),
    ]);
    $postId = (int) $pdo->lastInsertId();
    foreach ($post['categories'] as $categoryName) {
        $insertLink->execute([$postId, $ids[$categoryName]]);
    }
}

function readJson(string $path): array
{
    $data = json_decode((string) file_get_contents($path), true);
    if (!is_array($data)) {
        fwrite(STDERR, "Не читается {$path}\n");
        exit(1);
    }

    return $data;
}
