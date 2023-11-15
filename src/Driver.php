<?php

declare(strict_types=1);

namespace Effectra\Database;

/**
 * Represents the available database driver options.
 * 
 * @package Effectra\Database
 */
class Driver {

    /**
     * The SQLite database driver.
     */
    const SQLITE = 'sqlite';

    /**
     * The MySQL database driver.
     */
    const MYSQL = 'mysql';

    /**
     * The postGreSql database driver.
     */
    const POSTGRESQL = 'postgresql';
}
