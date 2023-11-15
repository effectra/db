<?php

declare(strict_types=1);

namespace Effectra\Database\Contracts;

interface SchemaInterface
{
        public function getColumn(): string;
        public function getDataType(): string;
        public function getDefaultValue(): mixed;
        public function isNullable(): bool;
        public function isAutoIncrement(): bool;
        public function isUnique(): bool;
        public function withColumn(string $column): self;
        public function withDataType(string $dataType): self;
        public function withDefaultValue(string $defaultValue): self;
        public function withNullable(bool $nullable): self;
        public function withAutoIncrement(bool $autoIncrement): self;
        public function withUnique(bool $unique): self;
        public function toArray():array;
}