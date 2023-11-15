<?php

declare(strict_types=1);

namespace Effectra\Database\Build;

use Effectra\Database\Contracts\SchemaInterface;
use Effectra\Database\Factory\SchemaFactory;
use Effectra\Database\Schema;

/**
 * Class BuildSchema
 *
 * This class is responsible for building Schema instances based on database table structure.
 * 
 * @package Effectra\Database
 */
class BuildSchema
{

    /**
     * @var SchemaInterface[] $schema An array to store Schema instances.
     */
    protected $schema;

    /**
     * BuildSchema constructor.
     *
     * @param array $structure The structure array representing the database table.
     */
    public function __construct(
        protected array $structure,
    ) {
        foreach ($structure as $item) {
            $this->schema[] = $this->build($item);
        }
    }

    /**
     * Get the array of Schema instances.
     *
     * @return SchemaInterface[] The array of Schema instances.
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Convert the Schema instances to an array.
     *
     * @return array The array representation of the Schema instances.
     */
    public function toArray(): array
    {
        return $this->getSchema();
    }

     /**
     * Build a Schema instance based on the provided table structure item.
     *
     * @param array $item The table structure item.
     * @return SchemaInterface The created Schema instance.
     */
    public function build(array $item): SchemaInterface
    {
        return SchemaFactory::create(
            [
                $this->setColumn($item['Field']),
                $this->setDataType($item['Type']),
                $this->setDefaultValue($item['Default']),
                $this->setNullable($item['Null']),
                $this->setAutoIncrement($item['Extra']),
                $this->setUnique($item['Extra'])
            ]
        );
    }

    /**
     * Set the column name.
     *
     * @param string $name The column name.
     * @return string The column name.
     */
    public function setColumn(string $name): string
    {
        return $name;
    }

    /**
     * Set the data type based on the provided MySQL data type.
     *
     * @param string $dataType The MySQL data type.
     * @return string The corresponding PHP data type.
     */
    public function setDataType(string $dataType): string
    {
        $dataType = explode('(', $dataType)[0];
        return match (strtolower($dataType)) {
            'timestamp', 'date', 'year', 'time', 'datetime', 'varchar', 'char', 'text', 'tinytext', 'mediumtext', 'longtext', 'blob', 'tinyblob', 'mediumblob', 'longblob', 'binary', 'varbinary', 'uuid', 'enum', 'set' => 'string',
            'int', 'bigint', 'tinyint', 'smallint', 'mediumint', 'integer' => 'integer',
            'decimal', 'bit', 'double', 'doubleprecision', 'float' => 'double',
            'json' => 'array',
            'boolean' => 'boolean',
            default => 'string'
        };
    }

    /**
     * Set the default value for the column.
     *
     * @param mixed $defaultValue The default value for the column.
     * @return mixed The processed default value.
     */
    public function setDefaultValue($defaultValue): mixed
    {
        if (in_array($defaultValue, ['NULL', null])) {
            return Schema::DEFAULT_VALUE_UNSET;
        }

        if ($defaultValue === 'current_timestamp()') {
            return Schema::DEFAULT_VALUE_CURRENT_TIMESTAMP;
        }

        return $defaultValue;
    }

    /**
     * Set the nullable status for the column.
     *
     * @param string $nullable The nullable status ('Yes' or 'No').
     * @return bool The nullable status as a boolean.
     */
    public function setNullable($nullable): bool
    {
        return $nullable === 'Yes' ? true : false;
    }

     /**
     * Set the auto-increment status for the column.
     *
     * @param string $autoIncrement The auto-increment status.
     * @return bool The auto-increment status as a boolean.
     */
    public function setAutoIncrement(string $autoIncrement): bool
    {
        return $autoIncrement === 'auto_increment' ? true : false;
    }

     /**
     * Set the unique status for the column.
     *
     * @param string $unique The unique status.
     * @return bool The unique status as a boolean.
     */
    public function setUnique(string $unique): bool
    {
        return $unique === 'unique' ? true : false;
    }
}
