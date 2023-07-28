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

$connection = new Connection(
    Driver::MYSQL,
    $mysqlConfig 
);
// Establish the database connection
$pdo = $connection->connect();
```

By default, the `Connection` class supports MySQL and SQLite database drivers. You can easily add support for additional database drivers by implementing the `DriverInterface` and configuring the `Connection` class accordingly.

### Query Execution

Once you have established a database connection, you can execute queries using the `DB` class. The `DB` class provides methods for executing common database operations such as `select`, `insert`, `update`, and `delete`.

```php
use Effectra\Database\DB;

// Create a new instance of the DB class
$db = new DB($pdo);

// Execute a select query
$data = $db->withQuery('SELECT * FROM users')->get();

// Execute an insert query
$db->table('users')->insert(['name' => 'Jane Doe']);


// Execute an update query
$db->table('users')->update(['name' => 'Jane Doe'],['id = 1']);

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

## Contributing

Contributions to the Effectra\Database package are welcome. If you find any issues or have suggestions for improvement, please open an issue or submit a pull request on the GitHub repository.

## License

The Effectra\Database package is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT). See the `LICENSE` file for more information.