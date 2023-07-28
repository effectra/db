<?php 


declare(strict_types=1);

namespace Effectra\Database\Contracts;

use PDO;

interface ConnectionInterface
{
     /**
     * Setups the SQLite database connection based on the provided configuration.
     *
     * @param array $config The configuration array for the SQLite database connection.
     * @return PDO The PDO instance representing the SQLite database connection.
     */
    public static function setup(array $config): PDO;
}