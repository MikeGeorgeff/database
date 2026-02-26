# Drivers

Drivers encapsulate the connection parameters for a specific database engine. Each driver implements `DriverInterface` and is passed to a `ConnectionManager`.

All drivers extend `AbstractDriver`, which sets default PDO attributes (`ERRMODE_EXCEPTION`, `FETCH_ASSOC`) and provides `setPdoOptions()` for overriding them.

## SQLite

```php
use Georgeff\Database\Connection\SqliteDriver;

// In-memory database (default)
$driver = new SqliteDriver();

// File-based database
$driver = new SqliteDriver('/path/to/database.sqlite');

// From an array
$driver = SqliteDriver::fromArray([]);
$driver = SqliteDriver::fromArray(['database' => '/path/to/database.sqlite']);
```

The `database` key is optional and defaults to `:memory:`.

SQLite does not support credentials, read replicas, or sticky writes.

## MySQL

```php
use Georgeff\Database\Connection\MySqlDriver;

$driver = new MySqlDriver(
    host:     'db.example.com',
    database: 'myapp',
    username: 'root',
    password: 'secret',
);

// From an array
$driver = MySqlDriver::fromArray([
    'hosts'    => ['write' => 'db.example.com'],
    'database' => 'myapp',
    'username' => 'root',
    'password' => 'secret',
]);
```

### Optional parameters

| Parameter | Default    | Description                        |
|-----------|------------|------------------------------------|
| `port`    | `'3306'`   | TCP port                           |
| `charset` | `'utf8mb4'`| Connection character set           |
| `sticky`  | `false`    | Enable sticky write behaviour      |

### Read replicas

Pass read hosts under `hosts.read`. The connection manager will load balance across them in round-robin order.

```php
$driver = MySqlDriver::fromArray([
    'hosts' => [
        'write' => 'primary.example.com',
        'read'  => ['replica1.example.com', 'replica2.example.com'],
    ],
    'database' => 'myapp',
    'username' => 'root',
    'password' => 'secret',
    'sticky'   => true,
]);
```

## PostgreSQL

```php
use Georgeff\Database\Connection\PgsqlDriver;

$driver = new PgsqlDriver(
    host:     'db.example.com',
    database: 'myapp',
    username: 'postgres',
    password: 'secret',
);

// From an array
$driver = PgsqlDriver::fromArray([
    'hosts'    => ['write' => 'db.example.com'],
    'database' => 'myapp',
    'username' => 'postgres',
    'password' => 'secret',
]);
```

### Optional parameters

| Parameter | Default     | Description                                  |
|-----------|-------------|----------------------------------------------|
| `port`    | `'5432'`    | TCP port                                     |
| `schema`  | `'public'`  | Search path schema (injected via `options=`) |
| `sslmode` | `'prefer'`  | SSL mode                                     |
| `sticky`  | `false`     | Enable sticky write behaviour                |

The `schema` value must be a valid PostgreSQL identifier (`[a-zA-Z_][a-zA-Z0-9_$]*`). An `InvalidArgumentException` is thrown if it is not.

### Read replicas

Same structure as MySQL:

```php
$driver = PgsqlDriver::fromArray([
    'hosts' => [
        'write' => 'primary.example.com',
        'read'  => ['replica1.example.com', 'replica2.example.com'],
    ],
    'database' => 'myapp',
    'username' => 'postgres',
    'password' => 'secret',
]);
```

## Customising PDO options

`AbstractDriver::setPdoOptions()` replaces the default PDO attribute map. This is intended to be called in a bootstrap or service provider layer, not by the connection manager.

```php
$driver->setPdoOptions([
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);
```
