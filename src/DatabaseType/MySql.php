<?php

declare(strict_types=1);

namespace Effectra\Database\DatabaseType;

use Effectra\Database\Contracts\DriverInterface;
use PDO;

/**
 * Class MySql
 *
 * Represents a MySQL database driver implementation.
 */
class MySql implements DriverInterface
{
    /**
     * Setups the MySQL database connection based on the provided configuration.
     *
     * @param array $config The configuration array for the MySQL database connection.
     * @return PDO The PDO instance representing the MySQL database connection.
     */
    public static function setup(array $config): PDO
    {
        extract($config);

        $db = sprintf("mysql:host=%s;dbname=%s", $host, $database);

        if (count($options) == 0) {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => sprintf('SET NAMES %s COLLATE %s', $charset, $collation)
            ];
        }

        return new PDO($db, $username, $password, $options);
    }
}
