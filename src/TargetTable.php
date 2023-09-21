<?php

declare(strict_types=1);

namespace Effectra\Database;

/**
 * Trait for working with database table information.
 */
trait TargetTable
{
    /**
     * Get the table description.
     *
     * @return array An array describing the table structure.
     */
    public function describeTable(): array
    {
        $query = "DESCRIBE {$this->getTable()}";
        return $this->query((string) $query)->fetch();
    }

    /**
     * Get information about the table's columns.
     *
     * @return array An array of column information.
     */
    public function getTableInfo(): array
    {
        return array_map(function ($column) {
            $sql_datatype = explode('(', $column['Type'])[0];
            return [
                $column['Field'] => [
                    'sql_datatype' => $sql_datatype,
                    'type' => $this->convertDataType($sql_datatype),
                    'default' => $column['Extra'] === 'auto_increment' ? 'auto_increment' : $column['Default'],
                    'null' => $column['Null']
                ]
            ];
        }, $this->describeTable());
    }

    /**
     * Get information about a specific column.
     *
     * @param string $column_name The name of the column.
     * @param array  $tableInfo   An array containing column information.
     *
     * @return array|null Column information if found, or null if not found.
     */
    public function getColumnInfo(string $column_name, array $tableInfo): ?array
    {
        foreach ($tableInfo as $col) {
            if (key($col) === $column_name) {
                return current($col);
            }
        }
        return null;
    }

    /**
     * Get an array of required columns.
     *
     * @param array $tableInfo An array containing column information.
     *
     * @return array An array of required column names.
     */
    public function requiredColumns(array $tableInfo):array
    {
        $requiredColumns = [];
    
        foreach ($tableInfo as $col) {
            $columnInfo = current($col);
    
            if ($columnInfo['default'] === null && $columnInfo['null'] === 'NO') {
                $requiredColumns[] = key($col);
            }
        }
        return $requiredColumns;
    }

    /**
     * Convert an SQL data type to a PHP data type.
     *
     * @param mixed $sql_datatype The SQL data type.
     *
     * @return string The corresponding PHP data type.
     */
    public function convertDataType($sql_datatype): string
    {
        return match (strtolower($sql_datatype)) {
            'timestamp', 'date', 'year', 'time', 'datetime', 'varchar', 'char', 'text', 'tinytext', 'mediumtext', 'longtext', 'blob', 'tinyblob', 'mediumblob', 'longblob', 'binary', 'varbinary', 'uuid', 'enum', 'set' => 'string',
            'int', 'bigint', 'tinyint', 'smallint', 'mediumint', 'integer' => 'integer',
            'decimal', 'bit', 'double', 'doubleprecision', 'float' => 'double',
            'json' => 'array',
            'boolean' => 'boolean',
            default => 'string'
        };
    }
}
