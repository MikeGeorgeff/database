# georgeff/database

A database library built on top of [aura/sql](https://github.com/auraphp/Aura.Sql) and [aura/sqlquery](https://github.com/auraphp/Aura.SqlQuery) that provides driver configuration, lazy connection management, a fluent query builder, and a clean execution layer.

## Installation

```bash
composer require georgeff/database
```

## Quick Start

```php
use Georgeff\Database\Connection\MySqlDriver;
use Georgeff\Database\Connection\ConnectionManager;
use Georgeff\Database\DatabaseManager;

$driver = MySqlDriver::fromArray([
    'hosts'    => ['write' => 'db.example.com'],
    'database' => 'myapp',
    'username' => 'root',
    'password' => 'secret',
]);

$db = new DatabaseManager(new ConnectionManager($driver));

// Fetch a single row
$user = $db->fetchOne(
    $db->select()->from('users')->where('id', 1)
);

// Fetch all rows
$users = $db->fetchAll(
    $db->select(['id', 'name'])->from('users')->where('active', 1)->orderBy('name', 'ASC')
);

// Insert a row
$db->fetchAffected(
    $db->insert()->into('users')->column('name', 'Alice')->column('email', 'alice@example.com')
);

// Update rows
$db->fetchAffected(
    $db->update()->table('users')->column('active', 0)->where('last_login', '2020-01-01', '<')
);

// Delete rows
$db->fetchAffected(
    $db->delete()->from('users')->where('active', 0)
);

// Transaction
$db->transaction(function () use ($db) {
    $db->fetchAffected($db->update()->table('accounts')->column('balance', 500)->where('id', 1));
    $db->fetchAffected($db->update()->table('accounts')->column('balance', 1500)->where('id', 2));
});
```

## Documentation

- [Drivers](.docs/drivers.md) — Configuring SQLite, MySQL, and PostgreSQL connections
- [Connection Manager](.docs/connection-manager.md) — Lazy connections, read replicas, and sticky writes
- [Query Builder](.docs/query-builder.md) — Building SELECT, INSERT, UPDATE, and DELETE queries
- [Transactions](.docs/transactions.md) — Wrapping operations in database transactions
- [Executor](.docs/executor.md) — Executing queries and fetching results
- [Database Manager](.docs/database-manager.md) — The unified entry point

## License

MIT. See [LICENSE](LICENSE).
