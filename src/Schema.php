<?php

declare(strict_types=1);

namespace Effectra\Database;

use Effectra\Database\Contracts\SchemaInterface;

/**
 * Class Schema
 *
 * Represents a database table schema for a specific column.
 * 
 * @package Effectra\Database
 */
class Schema implements SchemaInterface
{
    public const DEFAULT_VALUE_UNSET = 'DEFAULT_VALUE_UNSET';
    public const DEFAULT_VALUE_CURRENT_TIMESTAMP = 'DEFAULT_VALUE_CURRENT_TIMESTAMP';

    private string $column;
    private string $dataType;
    private mixed $defaultValue = self::DEFAULT_VALUE_UNSET;
    private bool $nullable = false;
    private bool $autoIncrement = false;
    private bool $unique = false;

    /**
     * Schema constructor.
     *
     * @param string $column The name of the column.
     * @param string $dataType The data type of the column.
     * @param mixed $defaultValue The default value for the column.
     * @param bool $nullable Indicates if the column is nullable.
     * @param bool $autoIncrement Indicates if the column is set to auto-increment.
     * @param bool $unique Indicates if the column values must be unique.
     */
    public function __construct(
        string $column,
        string $dataType,
        mixed $defaultValue = self::DEFAULT_VALUE_UNSET,
        bool $nullable = false,
        bool $autoIncrement = false,
        bool $unique = false
    ) {
        $this->column = $column;
        $this->dataType = $dataType;
        $this->defaultValue = $defaultValue;
        $this->nullable = $nullable;
        $this->autoIncrement = $autoIncrement;
        $this->unique = $unique;
    }

    /**
     * Get the name of the column.
     *
     * @return string The name of the column.
     */
    public function getColumn(): string
    {
        return $this->column;
    }

    /**
     * Get the data type of the column.
     *
     * @return string The data type of the column.
     */
    public function getDataType(): string
    {
        return $this->dataType;
    }

    /**
     * Get the default value for the column.
     *
     * @return mixed The default value for the column.
     */
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * Check if the column is nullable.
     *
     * @return bool True if the column is nullable, false otherwise.
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * Check if the column is set to auto-increment.
     *
     * @return bool True if the column is set to auto-increment, false otherwise.
     */
    public function isAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }

    /**
     * Check if the column values must be unique.
     *
     * @return bool True if the column values must be unique, false otherwise.
     */
    public function isUnique(): bool
    {
        return $this->unique;
    }

    // Methods for creating modified instances with updated properties

    /**
     * Create a new Schema instance with a different column name.
     *
     * @param string $column The new column name.
     * @return self The new Schema instance.
     */
    public function withColumn(string $column): self
    {
        $clone = clone $this;
        $clone->column = $column;
        return $clone;
    }

    /**
     * Create a new Schema instance with a different data type.
     *
     * @param string $dataType The new data type.
     * @return self The new Schema instance.
     */
    public function withDataType(string $dataType): self
    {
        $clone = clone $this;
        $clone->dataType = $dataType;
        return $clone;
    }

    /**
     * Create a new Schema instance with a different default value.
     *
     * @param mixed $defaultValue The new default value.
     * @return self The new Schema instance.
     */
    public function withDefaultValue(mixed $defaultValue): self
    {
        $clone = clone $this;
        $clone->defaultValue = $defaultValue;
        return $clone;
    }

    /**
     * Create a new Schema instance with a different nullable status.
     *
     * @param bool $nullable The new nullable status.
     * @return self The new Schema instance.
     */
    public function withNullable(bool $nullable): self
    {
        $clone = clone $this;
        $clone->nullable = $nullable;
        return $clone;
    }

    /**
     * Create a new Schema instance with a different auto-increment status.
     *
     * @param bool $autoIncrement The new auto-increment status.
     * @return self The new Schema instance.
     */
    public function withAutoIncrement(bool $autoIncrement): self
    {
        $clone = clone $this;
        $clone->autoIncrement = $autoIncrement;
        return $clone;
    }

    /**
     * Create a new Schema instance with a different unique status.
     *
     * @param bool $unique The new unique status.
     * @return self The new Schema instance.
     */
    public function withUnique(bool $unique): self
    {
        $clone = clone $this;
        $clone->unique = $unique;
        return $clone;
    }

    /**
     * Convert the Schema instance to an array.
     *
     * @return array The array representation of the Schema instance.
     */
    public function toArray(): array
    {
        return [
            'column' => $this->column,
            'dataType' => $this->dataType,
            'defaultValue' => $this->defaultValue,
            'nullable' => $this->nullable,
            'autoIncrement' => $this->autoIncrement,
            'unique' => $this->unique,
        ];
    }
}
