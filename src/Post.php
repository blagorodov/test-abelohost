<?php

readonly class Post
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @throws DateMalformedStringException
     */
    public function latestByCategory(): array
    {
        $sql = <<<'SQL'
            SELECT category_id, category_name, id, title, description, image, published_at
            FROM (
                SELECT
                    c.id AS category_id,
                    c.name AS category_name,
                    p.id,
                    p.title,
                    p.description,
                    p.image,
                    p.published_at,
                    ROW_NUMBER() OVER (
                        PARTITION BY c.id
                        ORDER BY p.published_at DESC, p.id DESC
                    ) AS rn
                FROM categories c
                INNER JOIN post_category pc ON pc.category_id = c.id
                INNER JOIN posts p ON p.id = pc.post_id
            ) ranked
            WHERE rn <= 3
            ORDER BY category_id ASC, rn ASC
            SQL;

        $categories = [];
        foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $id = (int) $row['category_id'];
            if (!isset($categories[$id])) {
                $categories[$id] = [
                    'id' => $id,
                    'name' => $row['category_name'],
                    'posts' => [],
                ];
            }
            $publishedAt = new DateTimeImmutable($row['published_at']);
            $categories[$id]['posts'][] = [
                'id' => (int) $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'image' => $row['image'],
                'published_at' => $publishedAt->format('d.m.Y'),
                'published_at_iso' => $publishedAt->format('Y-m-d'),
            ];
        }

        return array_values($categories);
    }

    public function countByCategory(int $categoryId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM post_category WHERE category_id = ?',
        );
        $statement->execute([$categoryId]);

        return (int) $statement->fetchColumn();
    }

    /** @throws DateMalformedStringException */
    public function pageByCategory(int $categoryId, string $sort, int $page): array
    {
        $orderBy = $sort === 'views'
            ? 'p.views DESC, p.id DESC'
            : 'p.published_at DESC, p.id DESC';
        $offset = ($page - 1) * 6;

        $sql = <<<SQL
            SELECT p.id, p.image, p.title, p.description, p.views, p.published_at
            FROM posts p
            INNER JOIN post_category pc ON pc.post_id = p.id
            WHERE pc.category_id = ?
            ORDER BY {$orderBy}
            LIMIT 6 OFFSET {$offset}
            SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([$categoryId]);

        $posts = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $publishedAt = new DateTimeImmutable($row['published_at']);
            $posts[] = [
                'id' => (int) $row['id'],
                'image' => $row['image'],
                'title' => $row['title'],
                'description' => $row['description'],
                'views' => (int) $row['views'],
                'published_at' => $publishedAt->format('d.m.Y'),
                'published_at_iso' => $publishedAt->format('Y-m-d'),
            ];
        }

        return $posts;
    }

    /** @throws DateMalformedStringException */
    public function open(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, image, title, description, body, views, published_at FROM posts WHERE id = ?',
        );
        $statement->execute([$id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        $this->pdo->prepare('UPDATE posts SET views = views + 1 WHERE id = ?')->execute([$id]);

        $categoriesStatement = $this->pdo->prepare(
            <<<'SQL'
                SELECT c.id, c.name
                FROM categories c
                INNER JOIN post_category pc ON pc.category_id = c.id
                WHERE pc.post_id = ?
                ORDER BY c.id ASC
                SQL
        );
        $categoriesStatement->execute([$id]);

        $categories = [];
        foreach ($categoriesStatement->fetchAll(PDO::FETCH_ASSOC) as $category) {
            $categories[] = [
                'id' => (int) $category['id'],
                'name' => $category['name'],
            ];
        }

        $publishedAt = new DateTimeImmutable($row['published_at']);

        return [
            'id' => (int) $row['id'],
            'image' => $row['image'],
            'title' => $row['title'],
            'description' => $row['description'],
            'body' => $row['body'],
            'views' => (int) $row['views'] + 1,
            'published_at' => $publishedAt->format('d.m.Y'),
            'published_at_iso' => $publishedAt->format('Y-m-d'),
            'categories' => $categories,
        ];
    }

    /** @throws DateMalformedStringException */
    public function similar(int $id): array
    {
        $sql = <<<'SQL'
            SELECT DISTINCT p.id, p.image, p.title, p.published_at
            FROM posts p
            INNER JOIN post_category pc ON pc.post_id = p.id
            WHERE pc.category_id IN (
                SELECT category_id FROM post_category WHERE post_id = ?
            )
            AND p.id <> ?
            ORDER BY p.published_at DESC, p.id DESC
            LIMIT 3
            SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([$id, $id]);

        $posts = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $publishedAt = new DateTimeImmutable($row['published_at']);
            $posts[] = [
                'id' => (int) $row['id'],
                'image' => $row['image'],
                'title' => $row['title'],
                'published_at' => $publishedAt->format('d.m.Y'),
                'published_at_iso' => $publishedAt->format('Y-m-d'),
            ];
        }

        return $posts;
    }
}
