<?php

declare(strict_types=1);

namespace Effectra\Database\Contracts;

use PDO;

/**
 * Interface ConnectionInterface
 *
 * Defines the contract for a database connection manager.
 * 
 * @package Effectra\Database
 */
interface ConnectionInterface
{
    /**
     * Get the current database driver.
     *
     * @return string The name of the database driver.
     */
    public function getDriver(): string;

    /**
     * Set the database driver.
     *
     * @param string $driver The name of the database driver.
     */
    public function setDriver(string $driver): void;

    /**
     * Establishes a database connection based on the configuration.
     *
     * @return PDO The PDO instance representing the database connection.
     */
    public function connect(): PDO;

    /**
     * Retrieves the appropriate database driver based on the given driver string.
     *
     * @return DriverInterface The instance of the DriverInterface implementation for the specified driver.
     * @throws DatabaseDriverException If the driver is not supported or does not exist.
     */
    public function getDatabaseDriver(): DriverInterface;
}
