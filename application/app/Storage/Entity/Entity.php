<?php

declare(strict_types=1);

namespace App\Storage\Entity;

use App\Storage\Entity\EntityInterface;
use App\Storage\Entity\Settings;
use App\Storage\Repository\RepositoryInterface;
use Exception;
use JsonSerializable;
use PDO;
use PDOStatement;

abstract class Entity implements EntityInterface, JsonSerializable
{
    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var Field[]
     */
    protected $modified = [];

    /**
     * @var boolean
     */
    protected $persisted = false;

    /**
     * @var Field
     */
    protected $primaryKey;

    public function __construct($properties = [], $isPersisted = false)
    {
        $this->boot($properties, $isPersisted);
    }

    /**
     * {@inheritdoc}
     */
    public function boot(array $properties = [], bool $isPersisted = false): EntityInterface
    {
        $this->settings = $this->define();
        $this->persisted = $isPersisted;
        $this->hydrate($properties);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @todo refatorar
     */
    public function hydrate(array $properties): void
    {
        foreach ($properties as $property => $value) {
            $setter = 'set' . ucfirst($property);

            $this->$setter($value, ($this->isPersisted() ? false : true));
        }
    }

    /**
     * {@inheritdoc}
     *
     * @todo refatorar
     */
    public function __call($name, $arguments)
    {
        $settings = $this->getSettings();

        $property = strlen($name) > 3 ? lcfirst(substr($name, 3)) : null;
        if ($property) {
            if (strpos($name, 'set') === 0) {
                $value = $arguments[0] ?? null;
                $modification = $arguments[1] ?? true;

                $propertyField = $settings->getField($property, $property);
                if ($propertyField instanceof Field) {
                    $property = $propertyField->getMapTo();

                    if ($modification) {
                        $this->addModified($property);
                    }
                }

                if (property_exists($this, $property) && isset($arguments[0])) {
                    $this->$property = $propertyField instanceof Field ? $propertyField->parse($value) : $value;

                    return $this;
                }
            }

            if (strpos($name, 'get') === 0) {
                if (property_exists($this, $property)) {
                    $propertyValue = $this->$property;
                    $propertyField = $settings->getField(null, $property);

                    if ($propertyField instanceof Field) {
                        $propertyValue = $propertyField->parse($propertyValue);
                    }

                    return $propertyValue;
                }
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function define(): Settings;

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey(): ?Field
    {
        if (!$this->primaryKey) {
            $settings = $this->getSettings();
            $fields = $settings->getFields();
            foreach ($fields as $field) {
                if ($field instanceof Field && $field->isPrimaryKey()) {
                    $this->primaryKey = $field;
                }
            }
        }

        return $this->primaryKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getPKName(): string
    {
        $primaryKey = $this->getPrimaryKey();
        if (!$primaryKey instanceof Field) {
            throw new Exception("Chave primária não definida!");
        }

        return $primaryKey->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getPKValue()
    {
        $primaryKey = $this->getPrimaryKey();
        if (!$primaryKey instanceof Field) {
            throw new Exception("Chave primária não definida!");
        }

        return $this->__call('get' . ucfirst($primaryKey->getMapTo()), []);
    }

    /**
     * {@inheritdoc}
     */
    public function modifiedFields(): array
    {
        return $this->modified;
    }

    /**
     * {@inheritdoc}
     */
    public function isModified(): bool
    {
        return $this->modified  && count($this->modified) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function isPersisted(): bool
    {
        return $this->persisted;
    }

    /**
     * {@inheritdoc}
     */
    public function addModified(string $property): EntityInterface
    {
        $settings = $this->getSettings();

        $field = $settings->getField(null, $property);
        if ($field) {
            $this->modified[$field->getMapTo()] = $field;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearModified(): EntityInterface
    {
        $this->modified = [];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPersisted(): EntityInterface
    {
        $this->persisted = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(): bool
    {
        if (!$this->isPersisted()) {
            return false;
        }

        $settings = $this->getSettings();

        $repository = $settings->getRepository();
        if (!$repository instanceof RepositoryInterface) {
            return false;
        }

        return $repository->remove($this);
    }

    /**
     * {@inheritdoc}
     */
    public function save(): bool
    {
        $settings = $this->getSettings();

        $repository = $settings->getRepository();
        if (!$repository instanceof RepositoryInterface) {
            return false;
        }

        if ($this->getPKValue()) {
            $this->setPersisted();
        }

        return $repository->save($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getSettings(): Settings
    {
        if (!$this->settings instanceof Settings) {
            throw new Exception('A entidade não foi definida!');
        }

        return $this->settings;
    }

    /**
     * {@inheritdoc}
     */
    public function insertSql(): ?PDOStatement
    {
        $settings = $this->getSettings();

        $repository = $settings->getRepository();
        if (!$repository instanceof RepositoryInterface) {
            return null;
        }

        $table = $repository->getSettings()->getTableName();

        $parameterList = [];
        foreach ($this->modifiedFields() as $property => $field) {
            if ($field instanceof Field) {
                $getter = 'get' . ucfirst($property);

                if (property_exists($this, $property) || method_exists($this, $getter)) {
                    $parameterList[] = [
                        'key' => ":$property",
                        'type' => $field->getTypePDO(),
                        'field' => $field->getName(),
                        'value' => $this->$getter(),
                    ];
                }
            }
        }

        if ($table && $parameterList) {
            $parameterFields = array_map(function ($paramenter) {
                return $paramenter['field'];
            }, $parameterList);

            $parameterKeys = array_map(function ($paramenter) {
                return $paramenter['key'];
            }, $parameterList);

            $queryFieldKeys = implode(', ', $parameterFields);
            $queryValueKeys = implode(', ', $parameterKeys);

            $statement = $repository->prepare("INSERT INTO $table ($queryFieldKeys) values ($queryValueKeys);");

            foreach ($parameterList as $parameter) {
                $statement->bindValue($parameter['key'], $parameter['value'], $parameter['type']);
            }

            return $statement;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function updateSql(): ?PDOStatement
    {
        $settings = $this->getSettings();

        $repository = $settings->getRepository();
        if (!$repository instanceof RepositoryInterface) {
            return null;
        }

        $table = $repository->getSettings()->getTableName();

        $parameterList = [];
        foreach ($this->modifiedFields() as $property => $field) {
            if ($field instanceof Field) {
                $getter = 'get' . ucfirst($property);

                if (property_exists($this, $property) || method_exists($this, $getter)) {
                    $fieldName = $field->getName();

                    $parameterList[] = [
                        'key' => ":$property",
                        'pair' => "$fieldName = :$property",
                        'type' => $field->getTypePDO(),
                        'value' => $this->$getter(),
                    ];
                }
            }
        }

        $primaryKeyValue = $this->getPKValue();
        $primaryKeyName = $this->getPKName();

        if ($table && $parameterList && $primaryKeyValue) {
            $parameterPair = array_map(function ($paramenter) {
                return $paramenter['pair'];
            }, $parameterList);

            $values = implode(', ', $parameterPair);


            $statement = $repository->prepare("UPDATE $table SET $values WHERE $primaryKeyName = :$primaryKeyName;");

            $statement->bindValue(":$primaryKeyName", $primaryKeyValue);

            foreach ($parameterList as $parameter) {
                $statement->bindValue($parameter['key'], $parameter['value'], $parameter['type']);
            }

            return $statement;
        }

        return null;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray(array $fields = []): array
    {
        if (!$fields) {
            $fields = $this->settings->getFields();
            $fields = array_map(function ($field) {
                if ($field instanceof Field) {
                    return $field->getMapTo();
                }

                return false;
            }, $fields);
        }

        $data = array_reduce($fields, function ($prev, $field) {
            $label = $field;
            $property = $field;

            if (is_array($field) && count($field) > 0) {
                $keys = array_keys($field);
                $label = array_shift($keys);
                $property = array_shift($field);
            }

            if ($property) {
                $prev[$label] = $property;
            }

            return $prev;
        }, []);

        return array_map(function ($property) {
            $getter = 'get' . ucfirst($property);

            return $this->$getter();
        }, $data);
    }
}
