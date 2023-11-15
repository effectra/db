<?php

declare(strict_types=1);

namespace Effectra\Database;

use Effectra\Database\Build\BuildSchema;
use Effectra\Database\Exception\DatabaseException;
use Effectra\Database\Contracts\DBInterface;
use Effectra\SqlQuery\Query;

/**
 * Class GetTableMetadata
 *
 * Represents a class for retrieving metadata information about a database table.
 * 
 * @package Effectra\Database
 */
class GetTableMetadata
{
     /**
     * @var DBInterface The database connection.
     */
    public function __construct(protected DBInterface $connection)
    {
    }

    /**
     * GetTableMetadata constructor.
     *
     * @param DBInterface $connection The database connection.
     */
    public function getTableMetadata(string $table): array
    {
        try {
            $query = Query::info()->listColumns($table);
            $this->connection->query((string) $query);
            $this->connection->run();
            $metadata = $this->connection->fetch();
            if ($metadata) {
                return $metadata;
            }
        } catch (\Exception $e) {
            throw new DatabaseException("Error statement: {$e->getMessage()}");
        }
    }

    /**
     * Get metadata information about a specific table.
     *
     * @param string $table The name of the table.
     *
     * @return array An array containing metadata information about the table.
     * @throws DatabaseException If an error occurs while fetching metadata.
     */
    public function getSchema($table)
    {

        return (new BuildSchema($this->getTableMetadata($table)))->getSchema();
    }
}
