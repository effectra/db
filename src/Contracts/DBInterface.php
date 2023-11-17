<?php

namespace Effectra\Database\Contracts;

use Effectra\DataOptimizer\Contracts\DataCollectionInterface;
use Effectra\DataOptimizer\Contracts\DataRulesInterface;
use Effectra\SqlQuery\Condition;
use PDO;
use PDOStatement;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class DBInterface
 *
 * The DBInterface provides a convenient interface for interacting with a database using PDO.
 *
 * @package Effectra\Database
 */
interface DBInterface
{
        /**
         * Create a database connection using the provided connection instance.
         *
         * @param ConnectionInterface $connection The database connection instance.
         * @return void
         */

        public static function createConnection(ConnectionInterface $connection);
        /**
         * Set the event dispatcher instance for the database operations.
         *
         * @param EventDispatcherInterface $eventDispatcher The event dispatcher instance.
         * @return void
         */
        public static function setEventDispatcher(EventDispatcherInterface $eventDispatcher);

        /**
         * Get the current event dispatcher instance for the database operations.
         *
         * @return EventDispatcherInterface The event dispatcher instance.
         */
        public static function getEventDispatcher(): EventDispatcherInterface;

        /**
         * Get the PDO database connection.
         *
         * @return PDO The PDO database connection.
         */
        public static function getConnection(): PDO;

        /**
         * Set the current SQL query string.
         *
         * @param string $query The SQL query string to set.
         * @return self The DB instance with the specified table.
         */
        public function query(string $query): self;

        /**
         * Get the prepared statement instance.
         *
         * @return PDOStatement|false The prepared statement instance.
         */
        public function statement(): PDOStatement|false;

        /**
         * Create and return a new DB instance with the specified table name.
         *
         * @param string $table The name of the database table.
         * @return self The DB instance with the specified table.
         */
        public function table(string $table): self;

        /**
         * Begin a new database transaction.
         *
         * @return bool Returns true on success or false on failure.
         */
        public function beginTransaction(): bool;

        /**
         * Commit the current database transaction.
         *
         * @return bool Returns true on success or false on failure.
         */
        public function commit(): bool;


        /**
         * Quotes a string for use in a query.
         *
         * @param string $string The string to be quoted.
         * @param int    $type   The data type of the quoted value (e.g., PDO::PARAM_INT, PDO::PARAM_STR).
         *
         * @return string|false Returns the quoted string on success or false on failure.
         */
        public function quote(string $string, int $type = PDO::PARAM_STR): string|false;


        /**
         * Roll back the current database transaction.
         *
         * @return bool Returns true on success or false on failure.
         */
        public function rollback(): bool;


        /**
         * Get the ID of the last inserted row.
         *
         * @return string|false Returns the last inserted ID or false on failure.
         */
        public function lastInsertId(): string|false;

        /**
         * Check if a transaction is currently active.
         *
         * @return bool Returns true if a transaction is active, false otherwise.
         */
        public function inTransaction(): bool;


        /**
         * Get the SQLSTATE error code.
         *
         * @return string|null Returns the error code or null if no error occurred.
         */
        public function errorCode(): ?string;

        /**
         * Get extended error information.
         *
         * @return array{0:string, 1:int, 2:string} Returns an array of error information.
         */
        public function errorInfo();

        /**
         * Execute an SQL statement and return the number of affected rows.
         *
         * @param string $statement The SQL statement to execute.
         *
         * @return int|false Returns the number of affected rows or false on failure.
         */
        public function exec(string $statement): int|false;

        /**
         * Get the value of a PDO attribute.
         *
         * @param int $attribute The PDO attribute to retrieve.
         *
         * @return mixed Returns the attribute value.
         */
        public function getAttribute(int $attribute): mixed;

        /**
         * Get an array of available PDO drivers.
         *
         * @return array Returns an array of available PDO drivers.
         */
        public function getAvailableDrivers(): array;

        /**
         *  Set an attribute 
         * @param int $attribute
         * @param mixed $value
         * @return bool — TRUE on success or FALSE on failure.
         */
        public  function setAttribute(int $attribute, mixed $value): bool;

        /**
         * Execute the current SQL query with optional parameters.
         *
         * @param array|null $params Optional parameters to bind to the query.
         * @return bool True if the query was executed successfully, false otherwise.
         *
         * @throws \Exception If there's an error executing the query.
         */
        public function run(?array $params = null): bool;


        /**
         * Bind a parameter to a variable for a prepared statement.
         *
         * @param int|string $param        The parameter identifier or name.
         * @param mixed      &$var         The reference to the variable to bind.
         * @param int        $type         The data type of the parameter (e.g., PDO::PARAM_INT, PDO::PARAM_STR).
         * @param int|null   $maxLength    The length of the data type.
         * @param mixed      $driverOptions Additional driver-specific options (optional).
         *
         * @return self Returns the current instance of the class.
         */
        public function bindParam(int|string $param, mixed &$var, int $type = PDO::PARAM_STR, int $maxLength = null, mixed $driverOptions = null);

        /**
         * Binds multiple parameters to a prepared statement with optional options.
         *
         * @param array $params An associative array where keys are parameter names
         *                      and values are either the values to bind or an associative
         *                      array of options including 'value', 'type', 'maxLength',
         *                      and 'driverOptions'.
         *
         * @return self Returns the current instance of the class.
         */
        public function bindMultipleParams(array $params): self;

        /**
         * Fetch all rows from the executed query as an associative array.
         *
         * @return ?array An array of fetched data or null if no data is available.
         */
        public function fetch(): ?array;

        /**
         * Fetch all rows from the executed query as an array of objects.
         *
         * @return array|null An array of fetched data as objects or null if no data is available.
         */
        public function fetchAsObject(): array|null;

        /**
         * Fetch and optimize data using custom rules.
         *
         * @param DataRulesInterface $rules define data optimization rules .
         * @return array|null The optimized data based on the provided rules.
         */
        public function fetchPretty(DataRulesInterface $rules): ?array;

        /**
         * Fetch all rows from the executed query as an DataCollectionInterface.
         *
         * @return ?array An array of fetched data or null if no data is available.
         */
        public function fetchAsCollection(): ?DataCollectionInterface;

        /**
         * Get the data inserted during an insert operation.
         *
         * @return array The data inserted.
         */
        public function getDataInserted(): array;

        /**
         * Set the data inserted during an insert operation.
         *
         * @param array $dataInserted The data inserted.
         */
        public function setDataInserted(array $dataInserted): void;

        /**
         * Validate and set data to be inserted into the database.
         *
         * @param mixed $data The data to be inserted.
         * @return self The DB instance with the specified table.
         * @throws DataValidatorException If the data is not valid.
         */
        public function data($data): self;

        /**
         * Optimize data, Validate and set data to be inserted into the database.
         *
         * @param mixed $data The data to be inserted.
         *
         * @throws DataValidatorException If the data is not valid.
         */
        public function prettyData($data, callable $rules);

        /**
         * Insert data into the database table.
         *
         * @return bool True if the data was successfully inserted, false otherwise.
         */
        public function insert(): bool;

        /**
         * Update data in the database table based on specified conditions.
         *
         * @param array $data The data to be updated.
         * @param ?Condition $conditions The conditions that determine which rows to update.
         * @return bool True if the data was successfully updated, false otherwise.
         */
        public function update(?Condition $conditions = null): bool;

        /**
         * Delete a rox from the database by its primary key.
         *
         * @param string|int $id
         * @param string $keyName 
         * @return bool
         */
        public function deleteById(string|int $id, string $keyName = 'id'): bool;

        /**
         * Perform a model operation in a transaction.
         *
         * @param callable $callback
         * @param array ...$args
         * @return mixed
         */
        public function transaction(callable $callback, array ...$args);
}
