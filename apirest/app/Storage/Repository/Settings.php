<?php

declare(strict_types=1);

namespace App\Storage\Repository;

use App\Storage\Entity\EntityInterface;
use DI\Container;
use Exception;
use ReflectionClass;
use Slim\Factory\AppFactory;

class Settings
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $entityName;

    /**
     * @var EntityInterface
     */
    private $entity;

    public function __construct(string $tableName, string $entityName)
    {
        $this->tableName = $tableName;
        $this->entityName = $entityName;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function newEntity(): EntityInterface
    {
        if (!$this->entityName) {
            throw new Exception("Nenhuma entidade definida!");
        }

        $reflection = new ReflectionClass($this->entityName);

        $entity =  $reflection->newInstanceWithoutConstructor();

        if (!$entity instanceof EntityInterface) {
            throw new Exception("Entidade não implementa EntityInterface!");
        }

        $entity->boot();

        return $entity;
    }

    public function getEntity(): ?EntityInterface
    {
        if (!$this->entity) {
            $entity = $this->newEntity();
            if ($entity instanceof EntityInterface) {
                $this->entity = $entity;
            }
        }

        return $this->entity;
    }
}
