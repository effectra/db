<?php

declare(strict_types=1);

namespace Effectra\Database;

use Effectra\Database\Contracts\DriverInterface;
use Effectra\Database\DatabaseType\MySql;
use Effectra\Database\DatabaseType\PostgreSQL;
use Effectra\Database\DatabaseType\Sqlite;
use Effectra\Database\Exception\DatabaseDriverException;
use Effectra\Database\Exception\DatabaseException;
use PDO;
use PDOException;

/**
 * Class Connection
 *
 * Represents a database connection manager.
 */
class Connection
{

    public function __construct(
        protected string $driver,
        protected array $config
    ) {
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
            throw new DatabaseDriverException("Error Processing Driver");
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
        return match($this->driver){
            'mysql'=> new MySql(),
            'sqlite' => new Sqlite(),
            'postgresql' =>new PostgreSQL()
        };
    }
}
