<?php

readonly class Post
{
    public const PER_PAGE = 6;

    public function __construct(private PDO $pdo)
    {
    }

    public function countByCategory(int $categoryId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM post_category WHERE category_id = ?',
        );
        $statement->execute([$categoryId]);

        return (int) $statement->fetchColumn();
    }

    public function pageByCategory(int $categoryId, string $sort, int $page): array
    {
        $orderBy = $sort === 'views'
            ? 'p.views DESC, p.id DESC'
            : 'p.published_at DESC, p.id DESC';
        $offset = ($page - 1) * self::PER_PAGE;

        $sql = <<<SQL
            SELECT p.id, p.image, p.title, p.description, p.views, p.published_at
            FROM posts p
            INNER JOIN post_category pc ON pc.post_id = p.id
            WHERE pc.category_id = ?
            ORDER BY {$orderBy}
            LIMIT ? OFFSET ?
            SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(1, $categoryId, PDO::PARAM_INT);
        $statement->bindValue(2, self::PER_PAGE, PDO::PARAM_INT);
        $statement->bindValue(3, $offset, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function incrementViews(int $id): bool
    {
        $statement = $this->pdo->prepare('UPDATE posts SET views = views + 1 WHERE id = ?');
        $statement->execute([$id]);

        return $statement->rowCount() === 1;
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, image, title, description, body, views, published_at FROM posts WHERE id = ?',
        );
        $statement->execute([$id]);
        $post = $statement->fetch();
        if ($post === false) {
            return null;
        }

        $categories = $this->pdo->prepare(
            <<<'SQL'
                SELECT c.id, c.name
                FROM categories c
                INNER JOIN post_category pc ON pc.category_id = c.id
                WHERE pc.post_id = ?
                ORDER BY c.id ASC
                SQL
        );
        $categories->execute([$id]);
        $post['categories'] = $categories->fetchAll();

        return $post;
    }

    public function similar(int $id): array
    {
        $sql = <<<'SQL'
            SELECT p.id, p.image, p.title, p.published_at
            FROM post_category own
            INNER JOIN post_category pc ON pc.category_id = own.category_id AND pc.post_id <> own.post_id
            INNER JOIN posts p ON p.id = pc.post_id
            WHERE own.post_id = ?
            GROUP BY p.id
            ORDER BY COUNT(*) DESC, p.published_at DESC, p.id DESC
            LIMIT 3
            SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([$id]);

        return $statement->fetchAll();
    }
}
