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
            SELECT category_id, category_name, title, description, image, published_at
            FROM (
                SELECT
                    c.id AS category_id,
                    c.name AS category_name,
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
                'title' => $row['title'],
                'description' => $row['description'],
                'image' => $row['image'],
                'published_at' => $publishedAt->format('d.m.Y'),
                'published_at_iso' => $publishedAt->format('Y-m-d'),
            ];
        }

        return array_values($categories);
    }
}
