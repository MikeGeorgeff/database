# Transactions

`TransactionManager` wraps a callback in a database transaction. It commits on success and rolls back on any exception, which is then re-thrown.

```php
use Georgeff\Database\Transaction\TransactionManager;
use Georgeff\Database\Connection\ConnectionManager;

$transactionManager = new TransactionManager($connectionManager);
```

When using `DatabaseManager`, a `TransactionManager` is wired up internally and exposed via the `transaction()` method.

## Executing a transaction

```php
$transactionManager->execute(function () {
    // operations here
});
```

The callback receives no arguments. Any query building or execution should be performed via the `DatabaseManager` or `Executor` directly within the closure.

### Return values

The return value of the callback is passed through:

```php
$id = $transactionManager->execute(function () use ($db) {
    $db->fetchAffected($db->insert()->into('users')->column('name', 'Alice'));

    return $db->fetchValue($db->select(['lastval()']));
});
```

### Exception handling

If the callback throws any `Throwable`, the transaction is rolled back and the exception is re-thrown:

```php
try {
    $transactionManager->execute(function () {
        throw new RuntimeException('something went wrong');
    });
} catch (RuntimeException $e) {
    // transaction has been rolled back
}
```

## Checking transaction state

```php
$transactionManager->isTransacting(); // bool
```

Returns `true` while a transaction is active, `false` otherwise.

## Nested transactions

Nested transactions are not supported. Calling `execute()` from within an active transaction throws a `LogicException`:

```php
$transactionManager->execute(function () use ($transactionManager) {
    $transactionManager->execute(fn() => null); // throws LogicException
});
```
