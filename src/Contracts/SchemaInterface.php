<?php

declare(strict_types=1);

namespace Effectra\Database\Contracts;

interface SchemaInterface
{
        /**
         * Get the name of the column.
         *
         * @return string The name of the column.
         */
        public function getColumn(): string;

        /**
         * Get the data type of the column.
         *
         * @return string The data type of the column.
         */
        public function getDataType(): string;

        /**
         * Get the default value for the column.
         *
         * @return mixed The default value for the column.
         */
        public function getDefaultValue(): mixed;

        /**
         * Check if the column is nullable.
         *
         * @return bool True if the column is nullable, false otherwise.
         */
        public function isNullable(): bool;

        /**
         * Check if the column is set to auto-increment.
         *
         * @return bool True if the column is set to auto-increment, false otherwise.
         */
        public function isAutoIncrement(): bool;

        /**
         * Check if the column values must be unique.
         *
         * @return bool True if the column values must be unique, false otherwise.
         */
        public function isUnique(): bool;

        /**
         * Create a new Schema instance with a different column name.
         *
         * @param string $column The new column name.
         * @return self The new Schema instance.
         */
        public function withColumn(string $column): self;

        /**
         * Create a new Schema instance with a different data type.
         *
         * @param string $dataType The new data type.
         * @return self The new Schema instance.
         */
        public function withDataType(string $dataType): self;

        /**
         * Create a new Schema instance with a different default value.
         *
         * @param mixed $defaultValue The new default value.
         * @return self The new Schema instance.
         */
        public function withDefaultValue(mixed $defaultValue): self;

        /**
         * Create a new Schema instance with a different nullable status.
         *
         * @param bool $nullable The new nullable status.
         * @return self The new Schema instance.
         */
        public function withNullable(bool $nullable): self;

        /**
         * Create a new Schema instance with a different auto-increment status.
         *
         * @param bool $autoIncrement The new auto-increment status.
         * @return self The new Schema instance.
         */
        public function withAutoIncrement(bool $autoIncrement): self;

        /**
         * Create a new Schema instance with a different unique status.
         *
         * @param bool $unique The new unique status.
         * @return self The new Schema instance.
         */
        public function withUnique(bool $unique): self;

        /**
         * Convert the Schema instance to an array.
         *
         * @return array The array representation of the Schema instance.
         */
        public function toArray(): array;
}