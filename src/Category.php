<?php

readonly class Category
{
    public function __construct(private PDO $pdo)
    {
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, description FROM categories WHERE id = ?',
        );
        $statement->execute([$id]);

        return $statement->fetch() ?: null;
    }

    public function withLatestPosts(): array
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
        foreach ($this->pdo->query($sql) as $row) {
            $id = $row['category_id'];
            $categories[$id] ??= [
                'id' => $id,
                'name' => $row['category_name'],
                'posts' => [],
            ];
            $categories[$id]['posts'][] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'image' => $row['image'],
                'published_at' => $row['published_at'],
            ];
        }

        return array_values($categories);
    }
}
