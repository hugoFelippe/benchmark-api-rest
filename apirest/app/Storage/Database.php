<?php

namespace App\Storage;

use Exception;
use PDO;
use PDOException;

class Database implements DatabaseInterface
{
    private $connection = null;

    public function __construct(
        string $driver,
        string $host,
        string $port,
        string $database,
        string $username,
        string $password,
        string $charset
    ) {
        $this->connection = new PDO(
            "$driver:host=$host;port=$port;dbname=$database;charset=$charset;",
            $username,
            $password
        );
    }

    public function getConnection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        throw new DatabaseException("Conexão não iniciada");
    }
}
