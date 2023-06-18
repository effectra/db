<?php

declare(strict_types=1);

namespace Effectra\Database;

use Effectra\SqlQuery\Query;
use PDO;
use PDOStatement;

/**
 * Class DB
 *
 * The DB class provides a convenient interface for interacting with a database using PDO.
 */
class DB
{
    protected PDO|null  $conn = null;
    protected PDOStatement|false $stmt = false;
    protected string  $q = "";
    protected string  $table = "";
    protected array $data = [];

    const FETCH_ASSOC = PDO::FETCH_ASSOC;

    /**
     * DB constructor.
     *
     * @param PDO|null $conn The PDO connection instance.
     */
    public function __construct(PDO $conn = null)
    {
        if ($conn) {
            $this->conn = $conn;
        }
    }

    /**
     * Get the PDO connection instance.
     *
     * @return PDO The PDO connection instance.
     */
    public function getConn(): PDO
    {
        return $this->conn;
    }

    /**
     * Get the current query.
     *
     * @return string The current query.
     */
    public function getQuery(): string
    {
        return $this->q;
    }

    /**
     * Get the prepared statement instance.
     *
     * @return PDOStatement|false The prepared statement instance.
     */
    public function getStatement(): PDOStatement|false
    {
        return $this->stmt;
    }

    /**
     * Set the PDO connection instance.
     *
     * @param PDO $conn The PDO connection instance.
     */
    public function setConn(PDO $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Set the query string.
     *
     * @param string $query The query string.
     * @return DB The updated DB instance.
     */
    public function withQuery($query): self
    {
        $clone = clone $this;
        $clone->q = (string) $query;
        return $clone;
    }

    /**
     * Set the prepared statement instance.
     *
     * @param PDOStatement|false $stmt The prepared statement instance.
     * @return DB The updated DB instance.
     */
    public function withStatement(PDOStatement|false $stmt): self
    {
        $clone = clone $this;
        $clone->stmt = $stmt;
        return $clone;
    }

    /**
     * Add to the current query string.
     *
     * @param string $query The query string to add.
     * @return string The updated query.
     */
    public function query(string $query = ''): string
    {
        if (!empty($query)) {
            $this->q .= $query;
        }
        return $this->getQuery();
    }

    /**
     * Run the query with optional parameters.
     *
     * @param array|null $params The query parameters.
     * @return bool True if the query executed successfully, false otherwise.
     */
    public function run(array|null $params = null): bool
    {
        $query = (string) $this->q;
        $this->stmt = $this->conn->prepare($query);
        return $this->stmt->execute($params);
    }

    /**
     * Execute the query and fetch all rows as an associative array.
     *
     * @param array|null $params The query parameters.
     * @return array The fetched data.
     */
    public function get(?array $params = null): array
    {
        $this->run($params);
        $this->stmt->setFetchMode(PDO::FETCH_ASSOC);
        $data = $this->stmt->fetchAll();

        return $data;
    }

    /**
     * Execute the query and fetch the first row as an object.
     *
     * @return mixed The fetched object.
     */
    public function getAsObject()
    {
        $this->run();
        $this->stmt->setFetchMode(PDO::FETCH_OBJ);
        $data = $this->stmt->fetchAll();
        return $this->prettyData($data)[0] ?? [];
    }

    /**
     * Set the data for binding parameters in the prepared statement.
     *
     * @param array $data The data to bind.
     * @return DB The updated DB instance.
     */
    public function data(array $data): self
    {
        $this->data = $data;
        foreach ($data as $key => $value) {
            if ($this->stmt !== false) {
                $this->stmt->bindParam(":$key", (string)  $value);
            }
        }
        return $this;
    }

    /**
     * Set the table name for the query.
     *
     * @param string $name The table name.
     * @return DB The updated DB instance.
     */
    public function table(string $name): self
    {
        $this->table = $name;
        return $this;
    }

    /**
     * Insert data into the table.
     *
     * @param mixed $data The data to insert.
     * @return void
     */
    public function insert($data): void
    {
        if ($this->isCollection($data)) {
            $query = "";
            foreach ($data as $item) {
                if (is_array($item)) {
                    $query .= Query::insert($this->table)->columns(array_keys($item))->values(array_values($item));
                }
            }
        } else {
            $query = Query::insert($this->table)->columns(array_keys($data))->values(array_values($data));
        }
        $this->withQuery((string) $query)->run();
    }

    /**
     * Update data in the table.
     *
     * @param mixed $data The data to update.
     * @param mixed $conditions The update conditions.
     * @return void
     */
    public function update($data, $conditions = null): void
    {
        if ($this->isCollection($data)) {
            $query = "";
            foreach ($data as $item) {
                if (is_array($item)) {
                    $query = Query::update($this->table)
                        ->columns(array_keys($item))
                        ->values(array_values($item))
                        ->where($conditions);
                }
            }
        } else {
            $query = Query::update($this->table)
                ->columns(array_keys($data))
                ->values(array_values($data))
                ->where($conditions);
        }
        $this->withQuery((string) $query)->run($query->combineColumnsValues());
    }

    /**
     * Get the columns of the table.
     *
     * @return array|false The columns of the table.
     */
    public function getColumns(): array|false
    {
        $query = Query::describe($this->table);
        return  $this->withQuery($query)->get();
    }

    /**
     * Get the field names of the columns of the table.
     *
     * @return array|false The field names of the columns.
     */
    public function getColumnsField(): array|false
    {
        return array_map(fn ($col) => $col['Field'], $this->getColumns());
    }

    /**
     * Check if the data is a collection (multiple rows).
     *
     * @param mixed $data The data to check.
     * @return bool True if the data is a collection, false otherwise.
     */
    public function isCollection($data)
    {
        return is_array($data) && count($data) > 1;
    }

    /**
     * Check if the string is a valid JSON.
     *
     * @param string $data The string to check.
     * @return bool True if the string is valid JSON, false otherwise.
     */
    public function isJson(string $data):bool
    {
        json_decode($data);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * Format the fetched data by decoding JSON strings.
     *
     * @param array $data The fetched data.
     * @return array The formatted data.
     */
    public function prettyData($data)
    {
        if ($this->isCollection($data)) {
            foreach ($data as &$item) {
                foreach ($item as $key => $value) {
                    $decode = json_decode($value, true);
                    if ($this->isJson($value)) {
                        $item[$key] = $decode;
                    }
                }
            }
        }
        return  $data;
    }
}
