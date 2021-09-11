<?php

declare(strict_types=1);

namespace App\Storage\Repository;

use App\Storage\Entity\EntityInterface;
use PDO;
use PDOStatement;

interface RepositoryInterface
{
    /**
     * Retorna as definições do repositório
     *
     * @return Settings
     */
    public function define(): Settings;

    /**
     * Retorna as configurações do repositório
     *
     * @todo renomear para definitions
     *
     * @return Settings
     */
    public function getSettings(): Settings;

    /**
     * Retorna a conexão PDO
     *
     * @return PDO
     */
    public function getConnection(): PDO;

    /**
     * Retorna a string da query preparada
     *
     * @param PDOStatement $statement
     * @return string
     */
    public function getStatementQuery(PDOStatement $statement): string;

    /**
     * Remove um registro no banco de dados baseado na chave primária
     *
     * @param EntityInterface $entity
     * @return boolean
     */
    public function remove(EntityInterface $entity): bool;

    /**
     * Insere ou atualiza um registro no banco de dados baseado na chave primária
     *
     * @param EntityInterface $entity
     * @return boolean
     */
    public function save(EntityInterface $entity): bool;

    /**
     * Retorna a primeira entidade baseado na chave primária
     *
     * @param mixed $identifier
     * @return EntityInterface|null
     */
    public function getById($identifier): ?EntityInterface;

    /**
     * Prepara uma query para execução
     *
     * @param string $rawQuery
     * @return PDOStatement
     */
    public function prepare(string $rawQuery): PDOStatement;

    /**
     * Executa um PDOStatement e retorna o resultado
     *
     * @param PDOStatement $statement
     * @return array
     */
    public function execute(PDOStatement $statement): array;

    /**
     * Executa uma consulta e retorna os dados
     *
     * @param PDOStatement $statement
     * @return array
     */
    public function fetchAll(PDOStatement $statement): array;

    /**
     * Prepara e executa uma query
     *
     * @param string $rawQuery
     * @param boolean $saveLog
     * @return array
     */
    public function query(string $rawQuery, bool $saveLog = false): array;

    /**
     * Prepara e executa uma consulta
     *
     * @param string $rawQuery
     * @param boolean $saveLog
     * @return array
     */
    public function select(string $rawQuery, bool $saveLog = false): array;

    /**
     * Tenta rastrear a execução
     *
     * @param string $message
     * @param integer $code
     * @return array
     */
    public function trackError(string $message = '', int $code = 0): array;

    /**
     * Retorna os erros registrados
     *
     * @return array
     */
    public function getErrors(): array;
}
