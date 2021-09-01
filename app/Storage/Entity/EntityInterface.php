<?php

declare(strict_types=1);

namespace App\Storage\Entity;

use PDOStatement;

interface EntityInterface
{
    /**
     * Inicia uma entidade
     *
     * @param array $args
     * @param boolean $isPersisted
     * @return Settings
     */
    public function boot(array $properties = [], bool $isPersisted = false): EntityInterface;

    /**
     * Hidrata a entidade com os valores das propriedades
     *
     * @param array $properties
     * @return void
     */
    public function hydrate(array $properties): void;

    /**
     * Método magico com get & set das propriedades da entidade
     *
     * @param mixed $name
     * @param mixed $arguments
     * @return mixed
     */
    public function __call($name, $arguments);

    /**
     * Retorna as definições da entidade
     *
     * @return Settings
     */
    public function define(): Settings;

    /**
     * Retorna as configurações da entidade
     *
     * @todo renomear para definitions
     *
     * @return Settings
     */
    public function getSettings(): Settings;

    /**
     * Retorna a chave primária
     *
     * @return null|Field
     */
    public function getPrimaryKey(): ?Field;

    /**
     * Retorna o nome da propriedade que é chave primária
     *
     * @return string
     */
    public function getPKName(): string;

    /**
     * Retorna o valor da propriedade que é chave primária
     *
     * @return string
     */
    public function getPKValue();

    /**
     * Retorna os campos modificados
     *
     * @return Field[]
     */
    public function modifiedFields(): array;

    /**
     * Retorna se tem ou não modificações
     *
     * @return boolean
     */
    public function isModified(): bool;

    /**
     * Verifica se a entidade já foi inserida no banco de dados
     *
     * @return boolean
     */
    public function isPersisted(): bool;

    /**
     * Adiciona um campo na lista de propriedades modificadas
     *
     * @param string $property
     * @return EntityInterface
     */
    public function addModified(string $property): EntityInterface;

    /**
     * Limpa a lista de modificações
     *
     * @return EntityInterface
     */
    public function clearModified(): EntityInterface;

    /**
     * Informa que a entidade já existe no banco de dados
     *
     * @return EntityInterface
     */
    public function setPersisted(): EntityInterface;

    /**
     * Inicia a remoção da propria entidade no banco de dados
     *
     * @return boolean
     */
    public function delete(): bool;

    /**
     * Inicia a inserção ou atualização da entidade no banco de dados
     *
     * @return EntityInterface
     */
    public function save(): bool;

    /**
     * Retorna um statement PDO para inserção da entidade no banco de dados
     *
     * @return PDOStatement|null
     */
    public function insertSql(): ?PDOStatement;

    /**
     * Retorna um statement PDO para atualização da entidade
     *
     * @return PDOStatement|null
     */
    public function updateSql(): ?PDOStatement;

    public function toArray(array $fields = []): array;
}
