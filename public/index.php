<?php

require dirname(__DIR__) . '/src/bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

try {
    if ($path === '/' || $path === '/index.php') {
        render('home.tpl', 200, [
            'categories' => new Category(db())->withLatestPosts(),
            'is_home' => true,
        ]);
        return;
    }

    if (preg_match('#^/category/([1-9][0-9]*)$#', $path, $matches) === 1) {
        $category = new Category(db())->find((int) $matches[1]);
        if ($category === null) {
            render('404.tpl', 404);
            return;
        }

        $sort = ($_GET['sort'] ?? '') === 'views' ? 'views' : 'date';
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $posts = new Post(db());
        $total = $posts->countByCategory($category['id']);
        $pageCount = max(1, (int) ceil($total / Post::PER_PAGE));
        if ($page > $pageCount) {
            render('404.tpl', 404);
            return;
        }

        render('category.tpl', 200, [
            'page_title' => $category['name'],
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
        $id = (int) $matches[1];
        if (!$posts->incrementViews($id)) {
            render('404.tpl', 404);
            return;
        }

        $post = $posts->find($id);
        render('post.tpl', 200, [
            'page_title' => $post['title'],
            'post' => $post,
            'paragraphs' => explode("\n\n", $post['body']),
            'related' => $posts->similar($id),
        ]);
        return;
    }

    render('404.tpl', 404);
} catch (Throwable $e) {
    error_log((string) $e);
    render('500.tpl', 500);
}
