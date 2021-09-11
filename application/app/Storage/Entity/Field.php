<?php

declare(strict_types=1);

namespace App\Storage\Entity;

use PDO;

class Field
{
    /**
     * Tipo boleano
     */
    public const BOOL_TYPE = PDO::PARAM_BOOL;

    /**
     * Tipo flutuante
     */
    public const FLOAT_TYPE = 7;

    /**
     * Tipo inteiro
     */
    public const INT_TYPE = PDO::PARAM_INT;

    /**
     * Tipo char
     */
    public const STR_CHAR_TYPE = PDO::PARAM_STR_CHAR;

    /**
     * Tipo string
     */
    public const STR_TYPE = PDO::PARAM_STR;

    private $name;
    private $mapTo;
    private $type;
    private $primaryKey;

    /**
     * @param string $name
     * @param integer $type
     * @param string $mapTo
     * @param boolean $isPrimaryKey
     */
    public function __construct(
        string $name,
        int $type = Field::STR_TYPE,
        string $mapTo = '',
        bool $isPrimaryKey = false
    ) {
        $this->name = $name;
        $this->mapTo = $mapTo !== '' ? $mapTo : $name;
        $this->type = $type;
        $this->primaryKey = $isPrimaryKey;
    }

    public function isPrimaryKey(): bool
    {
        return $this->primaryKey;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMapTo(): string
    {
        return $this->mapTo;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getTypePDO(): int
    {
        switch ($this->type) {
            case Field::BOOL_TYPE:
                return PDO::PARAM_BOOL;
                break;
            case Field::INT_TYPE:
                return PDO::PARAM_INT;
                break;
            case Field::STR_CHAR_TYPE:
                return PDO::PARAM_STR_CHAR;
                break;
            case Field::STR_TYPE:
            default:
                return PDO::PARAM_STR;
        }
    }

    public function parse($value)
    {
        switch ($this->type) {
            case Field::BOOL_TYPE:
                return is_null($value) ? NULL : !in_array($value, ['false', 'FALSE']) && boolval($value);
                break;
            case Field::FLOAT_TYPE:
                return is_null($value) ? NULL : floatval($value);
                break;
            case Field::INT_TYPE:
                return is_null($value) ? NULL : intval($value);
                break;
            case Field::STR_CHAR_TYPE:
            case Field::STR_TYPE:
            default:
                return html_entity_decode($value, ENT_QUOTES, "utf-8");
        }
    }
}
