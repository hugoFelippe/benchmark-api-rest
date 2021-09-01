<?php

declare(strict_types=1);

namespace App\Storage\Entity;

use App\Storage\DatabaseInterface;
use App\Storage\Repository\RepositoryInterface;
use DI\Container;
use Exception;
use Slim\Factory\AppFactory;

class Settings
{
    /**
     * @var RepositoryInterface|null
     */
    private $repository;

    /**
     * @var string
     */
    private $repositoryName;

    /**
     * @var Field[]
     */
    private $fields;

    public function __construct(string $defaultRepository, array $fields = [])
    {
        $this->repositoryName = $defaultRepository;
        $this->fields = array_values(array_filter($fields, function ($field) {
            return $field instanceof Field;
        }));
    }

    public function setRepository(RepositoryInterface $repository): Settings
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Retorna um campo com um dos valores passado
     *
     * @param string $fieldName
     * @param string $fieldProperty
     * @return Field|null
     */
    public function getField(string $fieldName = null, string $fieldProperty = null): ?Field
    {
        $result = array_filter($this->fields, function ($field) use ($fieldName, $fieldProperty) {
            return $field instanceof Field && (
                $field->getName() === $fieldName ||
                $field->getMapTo() === $fieldProperty
            );
        });

        return array_shift($result);
    }

    /**
     * Retorna o array de campos
     *
     * @return Field[]|null
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    /**
     * Retorna um repositorio se existir
     *
     * @return RepositoryInterface|null
     */
    public function getRepository(): ?RepositoryInterface
    {
        if (!$this->repository) {
            $app = AppFactory::create();
            $container = $app->getContainer();

            if (!$container instanceof Container) {
                throw new Exception('Container não iniciado!');
            }

            $repository = $container->get($this->repositoryName);
            $container->injectOn($repository);

            if ($repository instanceof RepositoryInterface) {
                $this->repository = $repository;
            }
        }

        return $this->repository;
    }
}
