<?php

require dirname(__DIR__) . '/src/bootstrap.php';
require_once dirname(__DIR__) . '/src/Post.php';
require_once dirname(__DIR__) . '/src/Category.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($path === null || $path === '') {
    $path = '/';
}

try {
    if ($path === '/' || $path === '/index.php') {
        render('home.tpl', 200, ['categories' => new Post(db())->latestByCategory()]);
        return;
    }

    if (preg_match('#^/category/([1-9][0-9]*)$#', $path, $matches) === 1) {
        $category = new Category(db())->find((int) $matches[1]);
        if ($category === null) {
            render('404.tpl', 404);
            return;
        }

        $sort = ($_GET['sort'] ?? '') === 'views' ? 'views' : 'date';
        $pageRaw = $_GET['page'] ?? null;
        $page = is_string($pageRaw) && preg_match('/^[1-9][0-9]*$/', $pageRaw) === 1
            ? (int) $pageRaw
            : 1;

        $posts = new Post(db());
        $total = $posts->countByCategory($category['id']);
        $pageCount = (int) ceil($total / 6);

        render('category.tpl', 200, [
            'category' => $category,
            'posts' => $posts->pageByCategory($category['id'], $sort, $page),
            'sort' => $sort,
            'page' => $page,
            'page_count' => $pageCount,
        ]);
        return;
    }

    if (preg_match('#^/post/([1-9][0-9]*)$#', $path, $matches) === 1) {
        $posts = new Post(db());
        $post = $posts->open((int) $matches[1]);
        if ($post === null) {
            render('404.tpl', 404);
            return;
        }

        $paragraphs = array_values(array_filter(
            array_map('trim', preg_split("/\n\s*\n/", $post['body']) ?: []),
            static fn (string $paragraph): bool => $paragraph !== '',
        ));
        unset($post['body']);

        render('post.tpl', 200, [
            'page_title' => $post['title'],
            'post' => $post,
            'paragraphs' => $paragraphs,
            'related' => $posts->similar($post['id']),
        ]);
        return;
    }

    render('404.tpl', 404);
} catch (PDOException) {
    render('500.tpl', 500, ['error' => 'База недоступна']);
} catch (Throwable $throwable) {
    render('500.tpl', 500, ['error' => $throwable->getMessage()]);
}
