<?php

$root = dirname(__DIR__);
require_once $root . '/src/bootstrap.php';

$categories = readJson($root . '/data/categories.json');
$posts = readJson($root . '/data/posts.json');

foreach ($posts as $post) {
    $source = $root . '/data/images/' . basename($post['image']);
    if (!is_file($source)) {
        fwrite(STDERR, "Нет файла {$source}\n");
        exit(1);
    }
}

$destDir = $root . '/public/images';
is_dir($destDir) || mkdir($destDir, 0775, true);

foreach ($posts as $post) {
    $name = basename($post['image']);
    if (!copy($root . '/data/images/' . $name, $destDir . '/' . $name)) {
        fwrite(STDERR, "Не удалось скопировать {$name}\n");
        exit(1);
    }
}

$pdo = db();
$pdo->beginTransaction();
$pdo->exec('DELETE FROM posts');
$pdo->exec('DELETE FROM categories');

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

$pdo->commit();

function readJson(string $path): array
{
    $data = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    if (!is_array($data)) {
        fwrite(STDERR, "Не читается {$path}\n");
        exit(1);
    }

    return $data;
}
