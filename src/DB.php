<?php

declare(strict_types=1);

namespace Effectra\Database;

use Effectra\Database\Contracts\ConnectionInterface;
use Effectra\Database\Contracts\DBInterface;
use Effectra\Database\Exception\DatabaseException;
use Effectra\Database\Exception\DataValidatorException;
use Effectra\DataOptimizer\Contracts\DataCollectionInterface;
use Effectra\DataOptimizer\Contracts\DataRulesInterface;
use Effectra\DataOptimizer\DataCollection;
use Effectra\DataOptimizer\DataOptimizer;
use Effectra\DataOptimizer\DataValidator;
use Effectra\SqlQuery\Condition;
use Effectra\SqlQuery\Operations\Insert;
use Effectra\SqlQuery\Query;
use PDO;
use PDOStatement;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class DB
 *
 * The DB class provides a convenient interface for interacting with a database using PDO.
 *
 * @package Effectra\Database
 */
class DB implements DBInterface
{
    private static ?PDO $CONNECTION = null;
    /**
     * @var string $QUERY The SQL query string.
     */
    protected static string $QUERY = '';

    /**
     * @var PDOStatement|false $statement The PDO statement or false if no statement is set.
     */
    protected PDOStatement|false $statement = false;

    /**
     * @var array $dataInserted The data inserted during operations.
     */
    protected array $dataInserted = [];

    /**
     * @var string $table The name of the database table.
     */
    protected string $table;

    protected static EventDispatcherInterface $eventDispatcher;

    /**
     * Create a database connection using the provided connection instance.
     *
     * @param ConnectionInterface $connection The database connection instance.
     * @return void
     */
    public static function createConnection(ConnectionInterface $connection)
    {
        static::$CONNECTION = $connection->connect();
        Query::driver($connection->getDriver());
    }

    /**
     * Set the event dispatcher instance for the database operations.
     *
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher instance.
     * @return void
     */
    public static function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        static::$eventDispatcher = $eventDispatcher;
    }

    /**
     * Get the current event dispatcher instance for the database operations.
     *
     * @return EventDispatcherInterface The event dispatcher instance.
     */
    public static function getEventDispatcher(): EventDispatcherInterface
    {
        return static::$eventDispatcher;
    }

    /**
     * Get the PDO database connection.
     *
     * @return PDO The PDO database connection.
     */
    public static function getConnection(): PDO
    {
        return static::$CONNECTION;
    }

    /**
     * Get the current SQL query string.
     *
     * @return string The current SQL query string.
     */
    private function getQuery(): string
    {
        return static::$QUERY;
    }

    /**
     * Set the current SQL query string.
     *
     * @param string $query The SQL query string to set.
     */
    private function setQuery(string $query): void
    {
        static::$QUERY = $query;
    }

    /**
     * Set the current SQL query string.
     *
     * @param string $query The SQL query string to set.
     * @return self The DB instance with the specified table.
     */
    public function query(string $query): self
    {
        $this->setQuery($query);
        return $this;
    }

    /**
     * Get the prepared statement instance.
     *
     * @return PDOStatement|false The prepared statement instance.
     */
    private function getStatement(): PDOStatement|false
    {
        return $this->statement;
    }

    /**
     * Get the prepared statement instance.
     *
     * @return PDOStatement|false The prepared statement instance.
     */
    public function statement(): PDOStatement|false
    {
        return $this->getStatement();
    }

    /**
     * Check statement instance
     * @return bool return true if statement is not false
     */
    private function hasStatement(): bool
    {
        return $this->statement !== false;
    }

    /**
     * Set the statement instance.
     *
     * @param  PDOStatement|false $statement The prepared statement instance.
     */
    private function setStatement(PDOStatement|false $statement): void
    {
        $this->statement  = $statement;
    }

    /**
     * Get table name
     * @return string the name of table
     */
    private function getTable(): string
    {
        return $this->table;
    }

    /**
     * Create table name.
     *
     * @param string $table The name of the database table.
     * @return void
     */
    private function setTable(string $table): void
    {
        $this->table = $table;
    }

    /**
     * Check table name is set.
     */
    private function isSetTableName()
    {
        if (!isset($this->table)) {
            throw new DatabaseException("table name is not set");
        }
    }

    /**
     * Create and return a new DB instance with the specified table name.
     *
     * @param string $table The name of the database table.
     * @return self The DB instance with the specified table.
     */
    public function table(string $table): self
    {
        $this->setTable($table);
        return $this;
    }

    /**
     * Begin a new database transaction.
     *
     * @return bool Returns true on success or false on failure.
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit the current database transaction.
     *
     * @return bool Returns true on success or false on failure.
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    /**
     * Quotes a string for use in a query.
     *
     * @param string $string The string to be quoted.
     * @param int    $type   The data type of the quoted value (e.g., PDO::PARAM_INT, PDO::PARAM_STR).
     *
     * @return string|false Returns the quoted string on success or false on failure.
     */
    public function quote(string $string, int $type = PDO::PARAM_STR): string|false
    {
        return $this->getConnection()->quote($string, $type);
    }

    /**
     * Roll back the current database transaction.
     *
     * @return bool Returns true on success or false on failure.
     */
    public function rollback(): bool
    {
        return $this->getConnection()->rollback();
    }

    /**
     * Get the ID of the last inserted row.
     *
     * @return string|false Returns the last inserted ID or false on failure.
     */
    public function lastInsertId(): string|false
    {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Check if a transaction is currently active.
     *
     * @return bool Returns true if a transaction is active, false otherwise.
     */
    public function inTransaction(): bool
    {
        return $this->getConnection()->inTransaction();
    }

    /**
     * Get the SQLSTATE error code.
     *
     * @return string|null Returns the error code or null if no error occurred.
     */
    public function errorCode(): ?string
    {
        return $this->getConnection()->errorCode();
    }

    /**
     * Get extended error information.
     *
     * @return array{0:string, 1:int, 2:string} Returns an array of error information.
     */
    public function errorInfo()
    {
        return $this->getConnection()->errorInfo();
    }

    /**
     * Execute an SQL statement and return the number of affected rows.
     *
     * @param string $statement The SQL statement to execute.
     *
     * @return int|false Returns the number of affected rows or false on failure.
     */
    public function exec(string $statement): int|false
    {
        return $this->getConnection()->exec($statement);
    }

    /**
     * Get the value of a PDO attribute.
     *
     * @param int $attribute The PDO attribute to retrieve.
     *
     * @return mixed Returns the attribute value.
     */
    public function getAttribute(int $attribute): mixed
    {
        return $this->getConnection()->getAttribute($attribute);
    }

    /**
     * Get an array of available PDO drivers.
     *
     * @return array Returns an array of available PDO drivers.
     */
    public function getAvailableDrivers(): array
    {
        return $this->getConnection()->getAvailableDrivers();
    }

    /**
     *  Set an attribute 
     * @param int $attribute
     * @param mixed $value
     * @return bool — TRUE on success or FALSE on failure.
     */
    public  function setAttribute(int $attribute, mixed $value): bool
    {
        return $this->getConnection()->setAttribute($attribute, $value);
    }

    /**
     * Execute the current SQL query with optional parameters.
     *
     * @param array|null $params Optional parameters to bind to the query.
     * @return bool True if the query was executed successfully, false otherwise.
     *
     * @throws \Exception If there's an error executing the query.
     */
    public function run(?array $params = null): bool
    {

        try {
            $stmt = $this->getConnection()->prepare(static::$QUERY);
            $this->setStatement($stmt);
            return $this->getStatement()->execute($params);
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }

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
    public function bindParam(int|string $param, mixed &$var, int $type = PDO::PARAM_STR, int $maxLength = null, mixed $driverOptions = null)
    {
        $this->getStatement()->bindParam($param, $var, $type, $maxLength, $driverOptions);
        return $this;
    }

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
    public function bindMultipleParams(array $params): self
    {
        foreach ($params as $param => $options) {
            // Set default values for type, maxLength, and driverOptions
            $type = PDO::PARAM_STR;
            $maxLength = null;
            $driverOptions = null;

            // Extract options if provided
            if (is_array($options)) {
                $type = $options['type'] ?? $type;
                $maxLength = $options['maxLength'] ?? $maxLength;
                $driverOptions = $options['driverOptions'] ?? $driverOptions;
                $value = $options['value'] ?? null;
            } else {
                $value = $options;
            }

            // Bind the parameter
            $this->getStatement()->bindParam($param, $value, $type, $maxLength, $driverOptions);
        }

        return $this;
    }

    /**
     * Fetch all rows from the executed query as an associative array.
     *
     * @return ?array An array of fetched data or null if no data is available.
     */
    public function fetch(): ?array
    {
        $this->run();
        if ($this->hasStatement()) {
            $this->getStatement()->setFetchMode(PDO::FETCH_ASSOC);
            $data = $this->getStatement()->fetchAll();
            $this->setStatement(false);
            return $data;
        }
        return null;
    }

    /**
     * Fetch all rows from the executed query as an array of objects.
     *
     * @return array|null An array of fetched data as objects or null if no data is available.
     */
    public function fetchAsObject(): array|null
    {
        $this->run();
        if ($this->hasStatement()) {
            $data = $this->getStatement()->fetchAll();
            $this->setStatement(false);
            return $data;
        }
        return null;
    }

    /**
     * Fetch and optimize data using custom rules.
     *
     * @param DataRulesInterface $rules define data optimization rules .
     * @return array|null The optimized data based on the provided rules.
     */
    public function fetchPretty(DataRulesInterface $rules): ?array
    {
        $data = $this->fetch();
        if (is_array($data)) {
            return (new DataOptimizer($data))->optimize($rules);
        }
        return null;
    }

     /**
     * Fetch all rows from the executed query as an DataCollectionInterface.
     *
     * @return ?array An array of fetched data or null if no data is available.
     */
    public function fetchAsCollection(): ?DataCollectionInterface
    {
        $data = $this->fetch();

        if(!is_array($data)){
            return null;
        }
        
        return new DataCollection($data);
    }

    /**
     * Get the data inserted during an insert operation.
     *
     * @return array The data inserted.
     */
    public function getDataInserted(): array
    {
        return $this->dataInserted;
    }

    /**
     * Set the data inserted during an insert operation.
     *
     * @param array $dataInserted The data inserted.
     */
    public function setDataInserted(array $dataInserted): void
    {
        $this->dataInserted = $dataInserted;
    }

    /**
     * Validate and set data to be inserted into the database.
     *
     * @param mixed $data The data to be inserted.
     * @return self The DB instance with the specified table.
     * @throws DataValidatorException If the data is not valid.
     */
    public function data($data): self
    {
        $validate = new DataValidator($data);

        if ($validate->isAssoc()) {
            $this->setDataInserted([$data]);
        } else if ($validate->isArrayOfAssoc()) {
            $this->setDataInserted($data);
        }

        $validate->validate();
        return $this;
    }

    /**
     * Optimize data, Validate and set data to be inserted into the database.
     *
     * @param mixed $data The data to be inserted.
     * @param DataRulesInterface $rules define rules using DataRulesInterface.
     * @throws DataValidatorException If the data is not valid.
     */
    public function prettyData($data, DataRulesInterface  $rules)
    {
        $data = (new DataOptimizer($data))->optimize($rules);
        $this->data($data);
    }

    /**
     * Insert data into the database table.
     *
     * @return bool True if the data was successfully inserted, false otherwise.
     */
    public function insert(): bool
    {
        $this->isSetTableName();

        foreach ($this->getDataInserted() as $item) {

            $query =  Query::insert($this->getTable(), Insert::INSERT_DATA);

            $query->data($item);

            $query->insertValuesModeSafe();

            $this->query((string) $query);

            $this->run($query->getParams());
        }

        return true;
    }

    /**
     * Update data in the database table based on specified conditions.
     *
     * @param array $data The data to be updated.
     * @param ?Condition $conditions The conditions that determine which rows to update.
     * @return bool True if the data was successfully updated, false otherwise.
     */
    public function update(?Condition $conditions = null): bool
    {
        $success = true;
        $this->isSetTableName();

        foreach ($this->getDataInserted() as $item) {

            $query = Query::update($this->getTable());

            if ($conditions instanceof Condition) {
                $query->whereConditions($conditions);
            }

            $query->data($item);

            $query->insertValuesModeSafe();

            $this->query((string) $query);

            $success = $this->run($query->getParams());
        }

        return $success;
    }

    /**
     * Delete a rox from the database by its primary key.
     *
     * @param string|int $id
     * @param string $keyName 
     * @return bool
     */
    public function deleteById(string|int $id, string $keyName = 'id'): bool
    {
        $query = Query::delete($this->getTable());
        $query->whereConditions((new Condition())->where([$keyName => ':' . $keyName]));

        return $this->query((string) $query)->run([$keyName => $id]);
    }

    /**
     * Perform a model operation in a transaction.
     *
     * @param callable $callback
     * @param array ...$args
     * @return mixed
     */
    public function transaction(callable $callback, array ...$args)
    {

        try {
            $this->beginTransaction();
            return call_user_func($callback, ...$args);
        } catch (\PDOException $e) {

            if ($this->inTransaction()) {
                $this->rollBack();
            }

            throw new DatabaseException($e->getMessage());
        }
    }
}