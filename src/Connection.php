<?php

declare(strict_types=1);

namespace Effectra\Database;

use Effectra\Config\ConfigDB;
use Effectra\Database\Contracts\DriverInterface;
use Effectra\Database\Exception\DatabaseDriverException;
use Effectra\Database\Exception\DatabaseException;
use PDO;
use PDOException;

/**
 * Class Connection
 *
 * Represents a database connection manager.
 */
class Connection extends ConfigDB
{
    /**
     * Establishes a database connection based on the configuration.
     *
     * @return PDO The PDO instance representing the database connection.
     * @throws DatabaseException If there is an error connecting to the database.
     */
    public function connect(): PDO
    {
        $config = $this->getConfig();

        try {
            return $this->getDatabaseDriver($config['driver'])->setup($config);
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage());
        }
    }

    /**
     * Retrieves the appropriate database driver based on the given driver string.
     *
     * @param string|DriverInterface $driver The driver string or an instance of DriverInterface.
     * @return DriverInterface The instance of the DriverInterface implementation for the specified driver.
     * @throws DatabaseDriverException If the driver is not supported or does not exist.
     */
    public function getDatabaseDriver(string $driver): DriverInterface
    {
        $drivers = [
            'mysql' => MySql::class,
            'sqlite' => Sqlite::class,
        ];

        if (isset($drivers[$driver])) {
            return new $drivers[$driver]();
        }

        if ($driver instanceof DriverInterface) {
            return $driver;
        }

        throw new DatabaseDriverException("This driver '$driver' does not exist.");
    }
}
