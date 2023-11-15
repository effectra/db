# Effectra\Database

Effectra\Database is a PHP package that provides database connection and query execution functionality. It offers a convenient interface for interacting with different database drivers and executing common database operations.

## Installation

You can install the Effectra\Database package via Composer. Simply run the following command:

```bash
composer require effectra/db
```

## Usage

### Connection

To establish a database connection, you need to create an instance of the `Connection` class and call the `connect` method. The `connect` method retrieves the database configuration from the provided configuration file and returns a PDO object representing the database connection.

```php
use Effectra\Database\Connection;
use Effectra\Database\Diver;
use Effectra\Config\ConfigDB;

// Create a new instance of the Connection class

$mysqlConfig = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'your_database_name',
    'username' => 'your_mysql_username',
    'password' => 'your_mysql_password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    // Add any additional options if needed
];

$connection = new Connection($mysqlConfig);
```

By default, the `Connection` class supports MySQL and SQLite database drivers. You can easily add support for additional database drivers by implementing the `DriverInterface` and configuring the `Connection` class accordingly.

### Query Execution

Once you have established a database connection, you can execute queries using the `DB` class. The `DB` class provides methods for executing common database operations such as `select`, `insert`, `update`, and `delete`.

```php
use Effectra\Database\DB;

// Establish the database connection
DB::createConnection($con);
// Establish the database event dispatcher
DB::setEventDispatcher(new EventDispatcher());

// Create a new instance of the DB class
$db = new DB();

// Execute a select query
$data = $db->withQuery('SELECT * FROM users')->get();

// Execute an insert query
$db->table('users')->data(['name' => 'Jane Doe','email'=> 'janeDoe@mail.com'])->insert();

// Execute an update query
$db->table('users')->data(['name' => 'Jane Doe'])->update((new Condition())->where(['id' => 2]));

```

The `DB` class provides a fluent interface for building and executing queries. You can chain methods to construct complex queries easily.


### Error Handling

If an error occurs during query execution, the `DB` class will throw a `DatabaseException`. You can catch and handle this exception to gracefully handle database errors.

```php
use Effectra\Database\Exception\DatabaseException;

try {
    $db->table('users')->insert( ['name' => 'John Doe']); // Missing 'email' field
} catch (DatabaseException $e) {
    // Handle the exception
    echo "Database Error: " . $e->getMessage();
}
```

# Model 

1. **Namespace:** The model is part of the `Effectra\Database` namespace.

2. **Traits:** The model uses the `ModelEventTrait` trait.

3. **Properties:**
   - `$connection`: An instance of the `DBInterface` representing the database connection.
   - `$schema`: An array containing the schema of the model.
   - `$entries`: An array containing the entries of the model.
   - `$table`: The name of the table associated with the model.
   - `$primaryKey`: The primary key for the model (default is 'id').
   - `$keyType`: The data type of the primary key (default is 'int').
   - `$incrementing`: Indicates if the model's ID is auto-incrementing (default is `true`).
   - `const CREATED_AT` and `const UPDATED_AT`: Constants representing the names of "created at" and "updated at" columns.
   - `$options`: Additional options for the model.
   - `private static $query`: A static property to store the last executed database query.

4. **Constructor:**
   - The constructor sets the table name if not provided.

5. **Methods:**
   - `isEntriesCreated()`: Checks if entries have been created for the model.
   - `createModelStructure()`: Creates the structure of the model, including schema and entries.
   - `getDatabaseConnection()`: Gets a new database connection instance.
   - `createSchema()`: Creates the schema for the model by fetching metadata from the database.
   - `isSchemaCreated()`: Checks if the schema has been created for the model.
   - `createEntriesFromSchema()`: Creates entries for the model based on the schema.
   - Various methods for setting, getting, and manipulating options.
   - `getSchema($property)`: Gets the schema entry for a specific property.
   - `getEntries()`: Gets the entries for the model.
   - `hasEntry($property)`: Checks if a specific entry exists in the model.
   - `getEntry($property)`: Gets the value of a specific entry in the model.
   - `setEntries($entries)`: Sets the entries for the model.
   - `setEntry($property, $value)`: Sets a specific entry for the model.
   - `removeEntry($property)`: Removes a specific entry from the model.
   - Various magic methods (`__invoke`, `__set`, `__get`, `__toString`, `__isset`, `__unset`, `__callStatic`, `__call`) for dynamic property access and method calls.
   - `toArray()`: Converts the model to an array representation.
   - `toJson($flags = 0, $depth = 512)`: Converts the model to its JSON representation.
   - `save()`: Saves the model to the database.
   - `update()`: Updates the model in the database.
   - `transaction($callback, ...$args)`: Performs a model operation in a transaction.
   - `saveInTransaction($data = [])`: Saves the model in a transaction.
   - `updateInTransaction()`: Updates the model in a transaction.
   - Methods for retrieving models from the database (`get`, `all`, `limit`, `find`, `findBy`, `search`, `where`, `between`).
   - Methods for deleting models from the database (`delete`, `deleteById`, `deleteByIds`, `deleteByIdsInTransaction`).
   - `truncate()`: Truncates the model's table.
   - `lastInsertId()`: Gets the last inserted ID for the model.
   - `validateId($id)`: Validates a model ID.
   - `setQuery($query)`: Sets the query instance for the model.
   - `getQueryUsed()`: Gets the query instance used by the model.
   - `getQueryUsedAsString()`: Gets the query instance used by the model as a string.
   - `dd()`: Dumps the model class using Symfony's VarDumper.

Overall, this model provides a flexible and extensible foundation for database interactions in a PHP application. It includes features for CRUD operations, query building, and transaction management. Additionally, it leverages traits for handling model events and uses Symfony's VarDumper for debugging purposes.


## Basic Usage

### create Model 

```php
class User extends Model {
    
}
```

### Retrieve a Record

```php

$user = User::find(1);
print_r($user);

echo $user->id;
// or use method
echo $user->getId();

```
### Update a Record

```php

$user->name = 'Foo Bar';
// or use method
$user->setName('Foo Bar');

$user->update();
```
### Save a Record

```php
$user = new User();

$user->name = 'Foo Bar';
$user->email = 'FooBar@email.com';

// or use method
$user
    ->setName('Foo Bar');
    ->setEmail('FooBar@email.com');

$user->save();
```

## Contributing

Contributions to the Effectra\Database package are welcome. If you find any issues or have suggestions for improvement, please open an issue or submit a pull request on the GitHub repository.

## License

The Effectra\Database package is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT). See the `LICENSE` file for more information.