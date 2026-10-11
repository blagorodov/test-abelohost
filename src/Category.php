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
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
        ];
    }
}
