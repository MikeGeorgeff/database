# Database Manager

`DatabaseManager` is the main entry point. It wires together a `ConnectionManager`, `QueryBuilder`, `TransactionManager`, and `Executor` behind a single unified API.

```php
use Georgeff\Database\Connection\MySqlDriver;
use Georgeff\Database\Connection\ConnectionManager;
use Georgeff\Database\DatabaseManager;

$driver  = MySqlDriver::fromArray([...]);
$manager = new DatabaseManager(new ConnectionManager($driver));
```

## Interface

`DatabaseManager` implements `DatabaseManagerInterface`, which extends both `QueryBuilderInterface` and `ExecutorInterface`. This means a `DatabaseManager` instance can be passed wherever a `QueryBuilderInterface` or `ExecutorInterface` is expected:

```php
function listUsers(QueryBuilderInterface $builder): array { ... }
function countUsers(ExecutorInterface $executor): int { ... }

listUsers($manager);  // valid
countUsers($manager); // valid
```

## Query building

The query builder methods produce query objects but do not execute them. Each call returns a new instance.

```php
$manager->select();              // SelectInterface
$manager->select(['id', 'name']); // SelectInterface with specific columns
$manager->insert();              // InsertInterface
$manager->update();              // UpdateInterface
$manager->delete();              // DeleteInterface
```

See [Query Builder](query-builder.md) for the full query building API.

## Execution

Execution methods accept a query object and run it against the appropriate connection.

```php
// Fetch a single row or null
$user = $manager->fetchOne(
    $manager->select()->from('users')->where('id', 1)
);

// Fetch all matching rows
$users = $manager->fetchAll(
    $manager->select()->from('users')->where('active', 1)
);

// Fetch first column of each row
$ids = $manager->fetchCol(
    $manager->select(['id'])->from('users')
);

// Fetch first two columns as a key-value array
$nameById = $manager->fetchPairs(
    $manager->select(['id', 'name'])->from('users')
);

// Fetch a single scalar value
$count = $manager->fetchValue(
    $manager->select(['COUNT(*)'])->from('users')
);

// Execute a write and return affected row count
$affected = $manager->fetchAffected(
    $manager->insert()->into('users')->column('name', 'Alice')
);

// Execute any query, return raw PDOStatement
$statement = $manager->perform(
    $manager->select()->from('users')->limit(100)
);
```

See [Executor](executor.md) for details on each method.

## Last insert ID

After an INSERT, retrieve the auto-generated ID from the write connection:

```php
$manager->fetchAffected(
    $manager->insert()->into('users')->column('name', 'Alice')
);

$id = $manager->lastInsertId(); // '42'
```

For PostgreSQL, pass the sequence name:

```php
$id = $manager->lastInsertId('users_id_seq');
```

Returns `null` if no ID is available.

## Connection

```php
$manager->isConnected();   // bool — whether a connection has been established
$manager->disconnect();    // close the connection
$manager->inTransaction(); // bool — whether a transaction is currently active
```

## Transactions

```php
$manager->transaction(function () use ($manager) {
    $manager->fetchAffected(
        $manager->update()->table('accounts')->column('balance', 500)->where('id', 1)
    );
    $manager->fetchAffected(
        $manager->update()->table('accounts')->column('balance', 1500)->where('id', 2)
    );
});
```

The callback's return value is passed through. Any exception triggers a rollback and is re-thrown.

See [Transactions](transactions.md) for details.
