<?php

declare(strict_types=1);

namespace Effectra\Database\Drivers;

use Effectra\Database\Contracts\DriverInterface;
use Effectra\Database\Exception\DatabaseDriverException;
use PDO;

/**
 * Class Sqlite
 *
 * Represents a SQLite database driver implementation.
 */
class Sqlite implements DriverInterface
{
    /**
     * Setups the SQLite database connection based on the provided configuration.
     *
     * @param array $config The configuration array for the SQLite database connection.
     * @return PDO The PDO instance representing the SQLite database connection.
     * @throws DatabaseDriverException When required configuration is missing.
     */
    public static function setup(array $config): PDO
    {
        // Check if the required 'database' configuration key is set
        if (!isset($config['database'])) {
            throw new DatabaseDriverException("Required configuration 'database' is missing.");
        }

        // Create the database connection string
        $db = sprintf("sqlite:%s", $config['database']);

        // Create and return the PDO instance representing the SQLite database connection
        return new PDO($db);
    }
}
