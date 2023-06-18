<?php

declare(strict_types=1);

namespace Effectra\Database\DatabaseType;

use Effectra\Database\Contracts\DriverInterface;
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
     */
    public static function setup(array $config): PDO
    {
        extract($config);

        $db = sprintf("sqlite:%s", $database);

        return new PDO($db);
    }
}
