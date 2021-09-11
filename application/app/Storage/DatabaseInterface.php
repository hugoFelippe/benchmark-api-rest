<?php

namespace App\Storage;

use PDO;
use PDOException;

interface DatabaseInterface
{
    /**
     * Inicia o banco de dados
     *
     * @param string $driver
     * @param string $host
     * @param string $port
     * @param string $database
     * @param string $username
     * @param string $password
     * @param string $charset
     * @throws PDOException
     */
    public function __construct(
        string $driver,
        string $host,
        string $port,
        string $database,
        string $username,
        string $password,
        string $charset
    );

    /**
     * Retorna a conexão PDO
     *
     * @return PDO
     * @throws DatabaseException
     */
    public function getConnection(): PDO;
}
