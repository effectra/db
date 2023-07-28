<?php

declare(strict_types=1);

namespace Effectra\Database\DatabaseType;

use Effectra\Database\Contracts\DriverInterface;
use Effectra\Database\Exception\DatabaseDriverException;
use PDO;

/**
 * Class PostgreSQL
 *
 * Represents a PostgreSQL database driver implementation.
 */
class PostgreSQL implements DriverInterface
{
    /**
     * Setups the PostgreSQL database connection based on the provided configuration.
     *
     * @param array $config The configuration array for the PostgreSQL database connection.
     * @return PDO The PDO instance representing the PostgreSQL database connection.
     */
    public static function setup(array $config): PDO
    {
        static::requireConfig($config);

        $dsn = sprintf(
            "pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s",
            $config['host'],
            $config['port'],
            $config['database'],
            $config['username'],
            $config['password']
        );

        if (count($config['options']) == 0) {
            $config['options'] = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            ];
        }

        return new PDO($dsn, $config['username'], $config['password'], $config['options']);
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
            'username', 'password', 'host', 'port', 'database',
        ];
    
        foreach ($requiredConfig as $c) {
            if (!array_key_exists($c, $configs)) {
                throw new DatabaseDriverException("Missed required configuration: {$c}");
            }
        }
    }
}
