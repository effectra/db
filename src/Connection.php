<?php

declare(strict_types=1);

namespace Effectra\Database;

use Effectra\Database\Contracts\ConnectionInterface;
use Effectra\Database\Contracts\DriverInterface;
use Effectra\Database\Drivers\MySql;
use Effectra\Database\Drivers\PostgreSQL;
use Effectra\Database\Drivers\Sqlite;
use Effectra\Database\Exception\DatabaseDriverException;
use Effectra\Database\Exception\DatabaseException;
use PDO;
use PDOException;

/**
 * Class Connection
 *
 * Represents a database connection manager.
 * 
 * @package Effectra\Database
 */
class Connection implements ConnectionInterface
{
    /**
     * The name of the database driver.
     *
     * @var string
     */
    private $driver;

     /**
     * The configuration array containing connection details.
     *
     * @var array
     */
    private $config;

    /**
     * Connection constructor.
     *
     * @param array $config The configuration array containing connection details.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->driver = $config['driver'];
    }

    /**
     * Get the current database driver.
     *
     * @return string The name of the database driver.
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * Set the database driver.
     *
     * @param string $driver The name of the database driver.
     */
    public function setDriver(string $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * Establishes a database connection based on the configuration.
     *
     * @return PDO The PDO instance representing the database connection.
     * @throws DatabaseException If there is an error connecting to the database.
     */
    public function connect(): PDO
    {
        $driver = $this->getDatabaseDriver();
        if (!$driver instanceof DriverInterface) {
            throw new DatabaseDriverException("Error Processing Driver, driver must be instance of  Effectra\Database\Exception\DatabaseDriverException");
        }

        try {
            return $driver->setup($this->config);
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage());
        }
    }

    /**
     * Retrieves the appropriate database driver based on the given driver string.
     *
     * @return DriverInterface The instance of the DriverInterface implementation for the specified driver.
     * @throws DatabaseDriverException If the driver is not supported or does not exist.
     */
    public function getDatabaseDriver(): DriverInterface
    {
        return match ($this->driver) {
            'mysql' => new MySql(),
            'sqlite' => new Sqlite(),
            'postgresql' => new PostgreSQL(),
            default => throw new DatabaseDriverException("Unsupported database driver: {$this->driver}"),
        };
    }
}
