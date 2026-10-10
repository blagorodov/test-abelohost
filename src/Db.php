<?php

class Db
{
    private ?PDO $pdo = null;

    public function __construct(private array $config)
    {
    }

    public function pdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $this->config['MYSQL_HOST'],
            $this->config['MYSQL_PORT'],
            $this->config['MYSQL_DATABASE'],
        );

        $this->pdo = new PDO(
            $dsn,
            $this->config['MYSQL_USER'],
            $this->config['MYSQL_PASSWORD'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );

        return $this->pdo;
    }
}
