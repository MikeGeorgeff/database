# Connection Manager

`ConnectionManager` wraps a driver and manages lazy PDO connections. No actual database connection is made until one is first requested.

```php
use Georgeff\Database\Connection\MySqlDriver;
use Georgeff\Database\Connection\ConnectionManager;

$driver  = MySqlDriver::fromArray([...]);
$manager = new ConnectionManager($driver);
```

`ConnectionManager` implements `ConnectionManagerInterface`, which is the type accepted by `DatabaseManager`, `TransactionManager`, and `Executor`.

## Connections

```php
$write = $manager->getWriteConnection(); // ExtendedPdoInterface
$read  = $manager->getReadConnection();  // ExtendedPdoInterface
```

Both methods return an `Aura\Sql\ExtendedPdoInterface` instance. The connection is created lazily on the first call and reused on subsequent calls.

## Read Replicas

When the driver is configured with read hosts, `getReadConnection()` distributes reads across them in round-robin order.

If no read replicas are configured, `getReadConnection()` returns the write connection.

## Sticky Writes

When sticky mode is enabled on the driver, any call to `getWriteConnection()` sets an internal flag that causes all subsequent `getReadConnection()` calls within the same request to return the write connection instead. This prevents reading stale data after a write.

```php
// After a write, reads go to the write connection
$manager->getWriteConnection(); // sets the sticky flag
$manager->getReadConnection();  // returns the write connection
```

To reset the sticky flag (e.g. at the end of a request):

```php
$manager->resetStickyWrite();
```

## Disconnecting

```php
$manager->disconnect();
```

Closes all connections (write and all read replicas) and resets internal state. Safe to call when no connection has been established — it is a no-op in that case.

## Checking Connection State

```php
$manager->isConnected(); // bool
```

Returns `true` if a connection has been established, `false` if one has not yet been made or after `disconnect()` is called.
