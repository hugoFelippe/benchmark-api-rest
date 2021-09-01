<?php

declare(strict_types=1);

namespace App\Storage\Repository;

use App\Storage\DatabaseInterface;
use App\Storage\Entity\EntityInterface;
use App\Storage\Entity\Field;
use App\Storage\Repository\RepositoryInterface;
use Exception;
use PDO;
use PDOStatement;
use PDOException;
use Psr\Log\LoggerInterface;

abstract class Repository implements RepositoryInterface
{
    /**
     * @var PDO
     */
    private $connection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PDOStatement
     */
    private $statement;

    /**
     * @var array
     */
    private $errors;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * {@inheritDoc}
     */
    abstract public function define(): Settings;

    public function __construct(DatabaseInterface $database, LoggerInterface $logger)
    {
        $this->settings = $this->define();
        $this->connection = $database->getConnection();
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function getSettings(): Settings
    {
        if (!$this->settings instanceof Settings) {
            throw new Exception('A entidade não foi definida!');
        }

        return $this->settings;
    }

    /**
     * {@inheritDoc}
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatementQuery(PDOStatement $statement): string
    {
        ob_start();
        $statement->debugDumpParams();
        $dump = ob_get_contents();
        ob_end_clean();

        return preg_replace("/\r|\n/", "", $dump);
    }

    /**
     * {@inheritDoc}
     */
    public function prepare(string $rawQuery): PDOStatement
    {
        $this->statement = $this->connection->prepare($rawQuery);

        return $this->statement;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PDOStatement $statement): array
    {
        if ($statement->execute()) {
            if ($this->connection->lastInsertId() === 0) {
                $action = "updated";
            } else {
                $action = "inserted";
            }

            $results = [
                'lastInsertId' => $this->connection->lastInsertId(),
                'action' => $action
            ];
        } else {
            $errorInfo = $statement->errorInfo();
            $results = [
                'error' => implode('|', $errorInfo)
            ];

            $dump = $this->getStatementQuery($statement);
            $error = $this->trackError(implode('|', $errorInfo));

            $this->logger->error("SQL:\n$dump\n\nError:\n$error", $results);
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAll(PDOStatement $statement): array
    {
        if ($statement->execute()) {
            $results = [];

            do {
                $results[] = $statement->fetchAll(PDO::FETCH_ASSOC);
            } while ($statement->nextRowset());

            if (count($results) <= 1) {
                $results = array_shift($results);
            }
        } else {
            $errorInfo = $statement->errorInfo();
            $results = [
                'error' => implode('|', $errorInfo)
            ];

            $dump = $this->getStatementQuery($statement);
            $error = $this->trackError(implode('|', $errorInfo));

            $this->logger->error("SQL:\n$dump\n\nError:\n$error", $results);
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function query(string $rawQuery, bool $saveLog = false): array
    {
        $this->prepare($rawQuery);

        if ($saveLog) {
            $dump = $this->getStatementQuery($this->statement);
            $this->logger->info("SQL:\n$dump", []);
        }

        return $this->execute($this->statement);
    }

    /**
     * {@inheritDoc}
     */
    public function select(string $rawQuery, bool $saveLog = false): array
    {
        $this->prepare($rawQuery);

        if ($saveLog) {
            $dump = $this->getStatementQuery($this->statement);
            $this->logger->info("SQL:\n$dump", []);
        }

        return $this->fetchAll($this->statement);
    }

    /**
     * {@inheritDoc}
     */
    public function trackError(string $message = '', int $code = 0): array
    {
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 6);
        $errorStack = [];

        foreach ($stack as $triggerBy) {
            $errorStack[] = $triggerBy;
        }

        $error = [
            'message' => $message,
            'stack' => $errorStack
        ];

        $this->errors[$code][] = $error;

        return $error;
    }

    /**
     * {@inheritDoc}
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * {@inheritDoc}
     */
    public function newEntity(): ?EntityInterface
    {
        $entity = $this->getSettings()->newEntity();
        if (!$entity instanceof EntityInterface) {
            return null;
        }

        return $entity;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(EntityInterface $entity): bool
    {
        $primaryKeyField = $entity->getPrimaryKey();

        if (!$primaryKeyField instanceof Field) {
            return false;
        }

        $primaryKey = $entity->getPKValue();
        $primaryKeyName = $entity->getPKName();

        if (!$primaryKey || !$primaryKeyName) {
            return false;
        }

        if (!$this->settings instanceof Settings) {
            return false;
        }

        $table = $this->settings->getTableName();

        try {
            $result = $this->query("DELETE FROM `$table` WHERE `$primaryKeyName` = '$primaryKey';");

            return !array_key_exists('error', $result);
        } catch (PDOException $e) {
            $error = $this->trackError($e->getMessage());

            $this->logger->error("Error:\n$error", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function save(EntityInterface $entity): bool
    {
        if (!$entity->isModified()) {
            return false;
        }

        $primaryKey = $entity->getPrimaryKey();
        if (!$primaryKey instanceof Field) {
            return false;
        }

        $statement = null;
        if ($entity->isPersisted()) {
            if (null === $statement = $entity->updateSql()) {
                throw new Exception("Erro ao tentar gerar a sql de update!");
            }
        } else {
            if (null === $statement = $entity->insertSql()) {
                throw new Exception("Erro ao tentar gerar a sql de inserção!");
            }
        }

        $result = $this->execute($statement);

        if (!array_key_exists('error', $result)) {
            $entity->clearModified();

            if ($result['action'] === 'inserted') {
                $setter = 'set' . ucfirst($primaryKey->getMapTo());

                $entity->setPersisted()->$setter($result['lastInsertId']);

                return true;
            }

            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getById($identifier): ?EntityInterface
    {
        if (!$this->getSettings() instanceof Settings) {
            return false;
        }

        $entity = $this->settings->newEntity();
        if (!$entity instanceof EntityInterface) {
            return false;
        }

        $primaryKeyField = $entity->getPrimaryKey();
        if (!$primaryKeyField instanceof Field) {
            return false;
        }

        $table = $this->settings->getTableName();
        $primaryKey = $entity->getPKName();

        if (!$table || !$primaryKey) {
            return false;
        }

        try {
            /**
             * @todo preparar as variaveis antes do select
             */
            $result = $this->select("SELECT *
            FROM $table
            WHERE $primaryKey = '$identifier'
            LIMIT 1;");

            if ($result && !array_key_exists('error', $result)) {
                $data = array_shift($result);
                $entity->setPersisted();
                $entity->hydrate($data);

                return $entity;
            }
        } catch (PDOException $e) {
            $error = $this->trackError($e->getMessage());

            $this->logger->error("Error:\n$error", [
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }
}
