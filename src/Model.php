<?php

declare(strict_types=1);

namespace Effectra\Database;

use Bmt\NounConverter\NounConverter;
use Effectra\Database\Contracts\DBInterface;
use Effectra\Database\Contracts\SchemaInterface;
use Effectra\DataOptimizer\Contracts\DataCollectionInterface;
use Effectra\DataOptimizer\Contracts\DataRulesInterface;
use Effectra\DataOptimizer\DataCollection;
use Effectra\DataOptimizer\DataValidator;
use Effectra\SqlQuery\Condition;
use Effectra\SqlQuery\Operations\Insert;
use Effectra\SqlQuery\Operations\Select;
use Effectra\SqlQuery\Query;
use LogicException;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class Model
 *
 * Represents a base model for interacting with a database.
 *
 * @package Effectra\Database
 */
class Model
{
    use ModelEventTrait;

    /**
     * @var DBInterface The database connection instance.
     */
    protected DBInterface $connection;

    /**
     * @var SchemaInterface[] The schema of the model.
     */
    private array $schema = [];

    /**
     * @var array The entries of the model.
     */
    protected array $entries = [];

    /**
     * @var string The table associated with the model.
     */
    protected string $table;

    /**
     * @var string The primary key for the model.
     */
    protected string $primaryKey = 'id';

    /**
     * @var string The data type of the primary key.
     */
    protected string $keyType = 'int';

    /**
     * @var bool Indicates if the model's ID is auto-incrementing.
     */
    public bool $incrementing = true;

    /**
     * The name of the "created at" column.
     */
    const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
     */
    const UPDATED_AT = 'updated_at';

    /**
     * @var array Additional options for the model.
     */
    protected array $options = [];

    /**
     * @var string The last executed database query.
     */
    private static $query = "";


    /**
     * Model constructor.
     *
     * If the table is not set, it is automatically generated based on the class name.
     */

    public function __construct()
    {

        if (!isset($this->table)) {
            $this->table = $this->makeTableName();
        }
    }

    /**
     * Check if entries have been created for the model.
     *
     * @return bool
     */
    private function isEntriesCreated(): bool
    {
        return !empty($this->entries);
    }

    /**
     * Create the structure of the model, including schema and entries.
     */
    private function createModelStructure(): void
    {
        $this->createSchema();
        $this->createEntriesFromSchema();
    }

    /**
     * Get a new database connection instance.
     *
     * @return DBInterface
     */
    public static function getDatabaseConnection(): DBInterface
    {
        return new DB();
    }

    /**
     * Create the schema for the model by fetching metadata from the database.
     *
     * @return array The schema of the model.
     */
    private function createSchema(): array
    {
        $metadata = new GetTableMetadata($this->getDatabaseConnection());
        foreach ($metadata->getSchema($this->getTable()) as $item) {
            $this->schema[$item->getColumn()] = $item;
        }
        return $this->schema;
    }

    /**
     * Check if the schema has been created for the model.
     *
     * @return bool
     */
    private function isSchemaCreated(): bool
    {
        return !empty($this->schema);
    }

    /**
     * Create entries for the model based on the schema.
     */
    private function createEntriesFromSchema(): void
    {
        foreach ($this->schema as $name => $item) {
            $this->entries[$item->getColumn()] = $item->getDefaultValue();
        }
    }

    /**
     * Set the options for the model.
     *
     * @param array $options The options to be set.
     * @return $this
     */
    private function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }
    /**
     * Set a specific option for the model.
     *
     * @param string $name The name of the option.
     * @param mixed $value The value to be set.
     * @return $this
     */
    private function setOption($name, $value): self
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * Add a key-value pair to a specific option for the model.
     *
     * @param string $name The name of the option.
     * @param mixed $key The key to be added.
     * @param mixed $value The value to be added.
     * @return $this
     */
    private function addToOption($name, $key, $value): self
    {
        $this->options[$name][$key] = $value;
        return $this;
    }

    /**
     * Get all options for the model.
     *
     * @return array
     */
    private function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get a specific option for the model.
     *
     * @param string $name The name of the option.
     * @return mixed The value of the option or null if the option does not exist.
     */
    private function getOption($name): mixed
    {
        return $this->options[$name] ?? null;
    }

    /**
     * Check if a specific option exists for the model.
     *
     * @param string $name The name of the option.
     * @return bool
     */
    private function hasOption($name): bool
    {
        return key_exists($name, $this->options);
    }

    /**
     * Get the schema entry for a specific property.
     *
     * @param string $property The name of the property.
     * @return SchemaInterface The schema entry for the property.
     * @throws \InvalidArgumentException If the schema entry does not exist.
     */
    public function getSchema($property): SchemaInterface
    {
        if (isset($this->schema[$property])) {
            return $this->schema[$property];
        }
        throw new \InvalidArgumentException("The schema '$property' does not exist.");
    }

    /**
     * Get the entries for the model.
     *
     * @return array The entries for the model.
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * Check if a specific entry exists in the model.
     *
     * @param string $property The name of the property.
     * @return bool
     */
    public function hasEntry(string $property): bool
    {
        return array_key_exists($property, $this->entries);
    }

    /**
     * Check if a specific entry has a null value in the model.
     *
     * @param string $property The name of the property.
     * @return bool
     */
    public function hasEntryNullValue(string $property): bool
    {
        return $this->getEntry($property) === null;
    }

    /**
     * Get the value of a specific entry in the model.
     *
     * @param string $property The name of the property.
     * @return mixed The value of the entry.
     * @throws \InvalidArgumentException If the property does not exist.
     */
    public function getEntry(string $property)
    {
        if ($this->hasEntry($property)) {
            return $this->entries[$property];
        }
        throw new \InvalidArgumentException("The property '$property' does not exist.");
    }

    /**
     * Get the property name from a method name that was called.
     *
     * @param string $method The method name.
     * @return string The property name.
     */
    private function getEntryFromMethodCalled(string $method): string
    {
        return Utils::camelToUnderscore(lcfirst(substr($method, 3)));
    }

    /**
     * Set the entries for the model.
     *
     * @param array $entries The entries to be set.
     * @return $this
     */
    private function setEntries(array $entries): self
    {
        $this->entries = $entries;
        return $this;
    }

    /**
     * Set multiple entries for the model.
     *
     * @param array $entries An associative array of property-value pairs.
     * @return $this
     */
    public function setMultipleEntries(array $entries): self
    {
        foreach ($entries as $property => $value) {
            $this->setEntry($property, $value);
        }
        return $this;
    }

    /**
     * Set a specific entry for the model.
     *
     * @param string $property The name of the property.
     * @param mixed $value The value to be set.
     * @throws \InvalidArgumentException If the property type is not as expected.
     */
    public function setEntry($property, $value): void
    {
        if (!$this->isSchemaCreated()) {
            $this->createSchema();
        }
        $type = $this->getSchema($property)->getDataType();
        if (gettype($value) === $type) {
            $this->entries[$property] = $value;
            if ($this->hasOption('called_get')) {
                $this->addToOption('updated_values', $property, $value);
            }

            return;
        }

        throw new \InvalidArgumentException("the argument '$property' type must be {$type} ");
    }
    /**
     * Remove a specific entry from the model.
     *
     * @param string $property The name of the property to be removed.
     */
    public function removeEntry(string $property): void
    {
        unset($this->entries[$property]);
    }

    /**
     * Get the prefix of a method name.
     *
     * @param string $method The method name.
     * @return string The prefix of the method name.
     */
    private function getPrefixMethod(string $method): string
    {
        return substr($method, 0, 3);
    }

    /**
     * Generate a default table name based on the model class name.
     *
     * @return string The generated table name.
     */
    private function makeTableName(): string
    {
        $name = (new \ReflectionClass($this))->getShortName();
        return  strtolower((new NounConverter())->convertToPlural(Utils::camelToUnderscore($name)));
    }

    /**
     * Set the table name for the model.
     *
     * @param string $table The name of the table.
     * @return $this
     */
    public function setTable($table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Get the table name for the model.
     *
     * @return string The name of the table.
     */
    public function getTable(): string
    {
        return $this->table;
    }
    /**
     * Get the name of the primary key for the model.
     *
     * @return string The name of the primary key.
     */
    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    /**
     * Set the name of the primary key for the model.
     *
     * @param string $key The name of the primary key.
     * @return $this
     */
    public function setKeyName($key): self
    {
        $this->primaryKey = $key;
        return $this;
    }

    /**
     * Get the data type of the primary key for the model.
     *
     * @return string The data type of the primary key.
     */
    public function getKeyType(): string
    {
        return $this->keyType;
    }

    /**
     * Set the data type of the primary key for the model.
     *
     * @param string $type The data type of the primary key.
     * @return $this
     */
    public function setKeyType($type): self
    {
        $this->keyType = $type;
        return $this;
    }

    /**
     * Check if the model's ID is auto-incrementing.
     *
     * @return bool
     */
    public function getIncrementing(): bool
    {
        return $this->incrementing;
    }


    /**
     * Set whether the primary key is auto-incrementing.
     *
     * @param bool $value
     * @return self
     */
    public function setIncrementing($value): self
    {
        $this->incrementing = $value;

        return $this;
    }

    /**
     * Convert the model to an array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->getEntries();
    }
    /**
     * Convert the model to its JSON representation.
     *
     * @param int $flags
     * @param int $depth
     * @return string|false
     */
    public function toJson(int $flags = 0, int $depth = 512): string|false
    {
        return json_encode($this->getEntries(), $flags, $depth);
    }

    public function __invoke(): array
    {
        return $this->getEntries();
    }

    public function __set($property, $value)
    {
        $this->setEntry($property, $value);
    }

    public function __get($property)
    {
        return $this->getEntry($property);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function __isset(string $property): bool
    {
        return $this->hasEntry($property);
    }

    public function __unset(string $property): void
    {
        $this->removeEntry($property);
    }

    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }

    public function __call(string $method, $arguments)
    {
        if (method_exists($this, $method)) {
            return  $this->$method(...$arguments);
        }

        $prefix = $this->getPrefixMethod($method);

        $property = $this->getEntryFromMethodCalled($method);

        if ($prefix === 'get') {
            return $this->getEntry($property);
        }
        if ($prefix === 'set' && count($arguments) === 1) {
            $this->setEntry($property, $arguments[0]);
            return $this;
        }

        return null;
    }

    /**
     * Get the entries that are required.
     *
     * @param array $except
     * @return array
     */
    public function getRequiredEntries(array $except = []): array
    {
        $requiredColumns = [];

        foreach ($this->schema as $entry) {
            if ($this->isAutoIncrement($entry)) {
                $except[] = $entry->getColumn();
            }
            if ($entry->getDefaultValue() !== Schema::DEFAULT_VALUE_UNSET) {
                $except[] = $entry->getColumn();
            }
            if (
                !in_array($entry->getColumn(), $except)
            ) {
                $requiredColumns[] = $entry;
            }
        }
        return $requiredColumns;
    }


    /**
     * Get the required entries as an array.
     *
     * @param mixed $except
     * @return array
     */
    public function getRequiredEntriesAsArray($except = []): array
    {

        return array_map(fn (SchemaInterface $item) => $item->getColumn(), $this->getRequiredEntries($except));
    }

    /**
     * Get the timestamp format for the model.
     *
     * @return string
     */
    public static function timestampFormat(): string
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Create the model's timestamps.
     *
     * @return void
     */
    private function createTimestamp(): void
    {
        if (static::CREATED_AT !== null) {
            $this->setEntry(static::CREATED_AT, date($this->timestampFormat()));
        }
        $this->updateTimestamp();
    }

    /**
     * Update the model's timestamps.
     *
     * @return void
     */
    public function updateTimestamp()
    {
        if (static::UPDATED_AT !== null) {
            $this->setEntry(static::UPDATED_AT, date($this->timestampFormat()));
        }
    }

    /**
     * Get the model's timestamps as an array.
     *
     * @return array
     */
    public function getTimestamp(): array
    {
        return [
            static::CREATED_AT => $this->getEntry(static::CREATED_AT),
            static::UPDATED_AT => $this->getEntry(static::UPDATED_AT)
        ];
    }

    /**
     * Validate a date and time string.
     *
     * @param mixed $dateString
     * @return bool
     */
    public function validateDateTime($dateString): bool
    {
        $date = \DateTime::createFromFormat($this->timestampFormat(), $dateString);

        return $date && $date->format($this->timestampFormat()) === $dateString;
    }

    /**
     * Create a new instance of the model.
     *
     * @return static
     */
    public static function newInstance(): static
    {
        return new static();
    }

    /**
     * Create a new model instance and save it to the database.
     *
     * @param array $data
     * @param bool $useTransaction
     * @return void
     */
    public static function create(array $data, $useTransaction = false)
    {
        if (empty($data)) {
            throw new \InvalidArgumentException("Data array is empty.");
        }


        $success = true;
        $v = (new DataValidator($data));

        if ($v->isAssoc()) {
            $data = [$data];
        }
        if ($v->isArrayOfAssoc()) {
            foreach ($data as $item) {
                $model = static::newInstance();
                $model->setMultipleEntries($item);
                $save = $useTransaction ? $model->saveInTransaction() : $model->save();
                if (!$save) {
                    $success = false;
                }
            }
            return $success;
        }
        throw new \InvalidArgumentException("Invalid data format. Expected an array of associative arrays.");
    }

    /**
     * Save the model to the database.
     *
     * @return bool
     */
    public function save(): bool
    {
        $this->createModelStructure();
        $this->createTimestamp();

        if ($this->getEntry('id') == Schema::DEFAULT_VALUE_UNSET) {
            $this->removeEntry('id');
        }

        $query =  Query::insert($this->getTable(), Insert::INSERT_DATA);

        $query->data($this->getEntries());

        $query->insertValuesModeSafe();

        static::setQuery($query);

        if ($this->event('saving') === false) {
            return false;
        }

        $result =  $this->getDatabaseConnection()->query((string) $query)->run($query->getParams());

        if ($result) {
            $this->event('saved');
            return true;
        }

        return false;
    }

    /**
     * Update the model in the database.
     *
     * @return bool
     */
    public function update(): bool
    {

        $this->updateTimestamp();

        $data = $this->getOption('updated_values');

        if (!$data) {
            throw new LogicException("this method must used when call a get method like 'all, find, findBy, search ...'");
        }

        $id = (int) $this->getEntry($this->getKeyName());

        $query =  Query::update($this->getTable(), Insert::INSERT_DATA)

            ->where([$this->getKeyName() => ':' . $this->getKeyName()])
            ->data($data)
            ->insertValuesModeSafe();

        static::setQuery($query);

        $params =  array_merge($query->getParams(), [$this->getKeyName() => $id]);

        if ($this->event('updating') === false) {
            return false;
        }

        $result = $this->getDatabaseConnection()->query((string) $query)->run($params);

        if ($result) {
            $this->event('updated');
            return true;
        }

        return false;
    }

    /**
     * Perform a model operation in a transaction.
     *
     * @param callable $callback
     * @param array ...$args
     * @return void
     */
    public static function transaction(callable $callback, array ...$args)
    {
        return static::getDatabaseConnection()->transaction($callback, $args);
    }

    /**
     * Save the model in a transaction.
     *
     * @param array $data
     * @return void
     */
    public function saveInTransaction($data = [])
    {
        return  $this->transaction(fn () => $this->save($data));
    }

    /**
     * Update the model in a transaction.
     *
     * @return mixed
     */
    public function updateInTransaction()
    {
        return $this->transaction(fn () => $this->update());
    }

    /**
     * Retrieve a collection of models from the database.
     *
     * @param array|string $columns
     * @param callable|null $toQuery
     * @param DataRulesInterface|null $rules
     * @param self|null $model
     * @return DataCollectionInterface|null
     */
    public static function get(array|string $columns = '*', ?callable $toQuery = null, ?DataRulesInterface $rules = null,  ?self $model = null): ?DataCollectionInterface
    {

        $model = $model ?? static::newInstance();

        $query = Query::select($model->getTable());

        if ($columns === '*') {
            $query->all();
        }
        if (is_array($columns)) {
            $query->columns($columns);
        }

        if ($toQuery) {
            call_user_func($toQuery, $query);
        }

        static::setQuery($query);

        $statement = $model->getDatabaseConnection()->query((string) $query);

        if ($rules) {
            $data = $statement->fetchPretty($rules);
        } else {
            $data = $statement->fetch();
        }

        if (!$data) {
            return null;
        }

        return (new DataCollection($data))
            ->map(
                fn ($item) => static::newInstance()
                    ->setOption('called_get', [])
                    ->setEntries($item)
            );
    }

    /**
     * Retrieve all models from the database.
     *
     * @param array|string $columns
     * @param DataRulesInterface|null $rules
     * @return DataCollectionInterface|null
     */
    public static function all(array|string $columns = '*', ?DataRulesInterface $rules = null): ?DataCollectionInterface
    {
        return static::get($columns, null, $rules);
    }

    /**
     * Limit the number of results returned from the database.
     *
     * @param int $start_from
     * @param int|null $count_until
     * @param array|string $columns
     * @param DataRulesInterface|null $rules
     * @return DataCollectionInterface|null
     */
    public static function limit(int $start_from, ?int $count_until = null, array|string $columns = '*', ?DataRulesInterface $rules = null): ?DataCollectionInterface
    {
        return static::get($columns, fn (Select $q) => $q->limit($start_from, $count_until), $rules);
    }

    /**
     * Find a model by its primary key.
     *
     * @param int $id
     * @param array|string $columns
     * @param DataRulesInterface|null $rules
     * @return self|null
     */
    public static function find(int $id, array|string $columns = '*', ?DataRulesInterface $rules = null): ?self
    {
        $model = static::newInstance();

        $data = static::get($columns, fn ($query) => $query->where([$model->getKeyName() => $id]), $rules, $model);

        return $data ? $data->first() : null;
    }

    /**
     * Find models by the given attribute.
     *
     * @param string $term
     * @param mixed $value
     * @param array|string $columns
     * @param DataRulesInterface|null $rules
     * @return DataCollectionInterface|null
     */
    public static function findBy(string $term, $value, array|string $columns = '*', ?DataRulesInterface $rules = null): ?DataCollectionInterface
    {
        return static::get($columns, fn ($query) => $query->where([$term => $value]), $rules);
    }

    /**
     * Search for models using the given terms.
     *
     * @param array $terms
     * @param array|string $columns
     * @param DataRulesInterface|null $rules
     * @return DataCollectionInterface|null
     */
    public static function search(array $terms, array|string $columns = '*', ?DataRulesInterface $rules = null): ?DataCollectionInterface
    {
        if (empty($terms)) {
            throw new \InvalidArgumentException('Invalid terms.');
        }
        return static::get($columns, fn (Select $q) => $q->whereLike($terms), $rules);
    }

    /**
     * Filter models by the given conditions.
     *
     * @param Condition $conditions
     * @param array|string $columns
     * @param DataRulesInterface|null $rules
     * @return DataCollectionInterface|null
     */
    public static function where(Condition $conditions, array|string $columns = '*', ?DataRulesInterface $rules = null): ?DataCollectionInterface
    {
        return static::get($columns, fn (Select $q) => $q->whereConditions($conditions), $rules);
    }

    /**
     * Filter models by a range of values in a column.
     *
     * @param string $targetColumn
     * @param mixed $from
     * @param mixed $to
     * @param array|string $columns
     * @param DataRulesInterface|null $rules
     * @return DataCollectionInterface|null
     */
    public static function between(string $targetColumn, string|int|float $from, string|int|float $to, array|string $columns = '*', ?DataRulesInterface $rules = null): ?DataCollectionInterface
    {
        return static::get($columns, fn (Select $q) => $q->whereInBetween($targetColumn, $from, $to), $rules);
    }

    /**
     * Delete models from the database based on conditions.
     *
     * @param Condition $conditions
     * @param array|null $params
     * @param self|null $model
     * @return bool
     */
    public static function delete(Condition $conditions, ?array $params = null, ?self $model = null): bool
    {
        $model = $model ?? static::newInstance();

        if ($model->event('deleting') === false) {
            return false;
        }

        $query = Query::delete($model->getTable());
        $query->whereConditions($conditions);

        static::setQuery($query);

        $result = $model->getDatabaseConnection()->query((string) $query)->run($params);

        if ($result) {
            $model->event('deleted');
        }
        return $result;
    }

    /**
     * Delete a model from the database by its primary key.
     *
     * @param string|int $id
     * @return bool
     */
    public static function deleteById(string|int $id): bool
    {
        $model = static::newInstance();
        if (!$model->validateId($id)) {
            throw new \InvalidArgumentException("Error Processing ID($id),ID must be type {$model->getKeyType()}");
        }
        return static::delete(
            (new Condition())->where([$model->getKeyName() => ':' . $model->getKeyName()]),
            [$model->getKeyName() => $id],
            $model
        );
    }

    /**
     * Delete models from the database by their primary keys.
     *
     * @param array $ids
     * @return bool
     */
    public static function deleteByIds(array $ids): bool
    {
        $success = true;
        foreach ($ids as $id) {
            if (!static::deleteById($id)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Delete models from the database by their primary keys in a transaction.
     *
     * @param array $ids
     * @return mixed
     */
    public static function deleteByIdsInTransaction(array $ids): mixed
    {
        return static::transaction(fn () => static::deleteByIdsInTransaction($ids));
    }

    /**
     * Truncate the model's table.
     *
     * @return bool
     */
    public static function truncate(): bool
    {
        $model = static::newInstance();

        $query = Query::truncate($model->getTable());

        static::setQuery($query);

        return $model->getDatabaseConnection()->query((string) $query)->run();
    }

    /**
     * Get the last inserted ID for the model.
     *
     * @return int
     */
    public static function lastInsertId(): int
    {
        $model = static::newInstance();
        return (int) $model->getDatabaseConnection()->lastInsertId();
    }

    /**
     * Validate a model ID.
     *
     * @param mixed $id
     * @return bool
     */
    public function validateId($id): bool
    {
        $keyType = $this->getKeyType();

        if ($keyType === 'int') {
            if (is_integer($id)) {
                return true;
            }
            if (is_string($id) && preg_match('/^\d+$/', $id)) {
                return true;
            }
        }

        if ($keyType === 'string' && is_string($id)) {
            return true;
        }

        return false;
    }

    /**
     * Set the query instance for the model.
     *
     * @param mixed $query
     * @return void
     */
    public static function setQuery($query): void
    {
        static::$query = $query;
    }

    /**
     * Get the query instance used by the model.
     *
     * @return mixed
     */
    public static function getQueryUsed()
    {
        return static::$query;
    }

    /**
     * Get the query instance used by the model as a string.
     *
     * @return string
     */
    public static function getQueryUsedAsString(): string
    {
        return (string) static::getQueryUsed();
    }

    /**
     * dump model class using `Symfony\Component\VarDumper\VarDumper` package
     *
     * @return string
     */
    public function dd(): void
    {
        VarDumper::dump($this);
    }
}
