<?php

declare(strict_types=1);

namespace Effectra\Database\Drivers;

use Effectra\Database\Contracts\DriverInterface;
use Effectra\Database\Exception\DatabaseDriverException;
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
        static::requireConfig($config);

        $db = sprintf("mysql:host=%s;dbname=%s", $config['host'], $config['database']);

        if (count($config['options']) == 0) {
            $config['options'] = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => sprintf('SET NAMES %s COLLATE %s', $config['charset'], $config['collation'])
            ];
        }

        return new PDO($db, $config['username'], $config['password'], $config['options']);
    }

    /**
     * Ensures that all required configurations are present in the given array.
     *
     * @param array $configs The array of configurations to check.
     * @throws DatabaseDriverException Thrown if any required configuration is missing.
     */
    private static function requireConfig(array $configs): void
    {
        $requiredConfig = [
            'username', 'password', 'host', 'database', 'charset', 'collation',
        ];
    
        foreach ($requiredConfig as $c) {
            if (!array_key_exists($c, $configs)) {
                throw new DatabaseDriverException("Missed required configuration: {$c}");
            }
        }
    }
}
