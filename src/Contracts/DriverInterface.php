<?php

declare(strict_types=1);

namespace Effectra\Database\Contracts;

use PDO;

/**
 * Interface DriverInterface
 *
 * Represents a database driver interface.
 */
interface DriverInterface
{
    /**
     * Setups the database connection based on the provided configuration.
     *
     * @param array $config The configuration array for the database connection.
     * @return PDO The PDO instance representing the database connection.
     */
    public static function setup(array $config): PDO;
}
