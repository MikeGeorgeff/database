# Executor

`Executor` runs query objects against the database and returns results. Read operations are routed to the read connection; write operations are routed to the write connection.

```php
use Georgeff\Database\Execution\Executor;

$executor = new Executor($connectionManager);
```

When using `DatabaseManager`, an `Executor` is wired up internally and its methods are available directly on the manager.

## Read methods

All read methods accept a `SelectInterface` query.

### fetchOne

Returns the first matching row as an associative array, or `null` if no row is found.

```php
$user = $executor->fetchOne(
    $builder->select()->from('users')->where('id', 1)
);
// ['id' => 1, 'name' => 'Alice'] or null
```

### fetchAll

Returns all matching rows as an array of associative arrays.

```php
$users = $executor->fetchAll(
    $builder->select()->from('users')->where('active', 1)
);
// [['id' => 1, ...], ['id' => 2, ...]]
```

### fetchCol

Returns the values of the first column of each matching row as a sequential array.

```php
$ids = $executor->fetchCol(
    $builder->select(['id'])->from('users')->where('active', 1)
);
// [1, 2, 3]
```

### fetchPairs

Returns an associative array keyed by the first column with the second column as values.

```php
$nameById = $executor->fetchPairs(
    $builder->select(['id', 'name'])->from('users')->where('active', 1)
);
// [1 => 'Alice', 2 => 'Bob']
```

### fetchValue

Returns the value of the first column of the first matching row.

```php
$count = $executor->fetchValue(
    $builder->select(['COUNT(*)'])->from('users')
);
// 42
```

### count

Wraps the query in a `COUNT(*)` and returns the result as an integer. This is equivalent to calling `fetchValue` with `toCountSql()` rather than `toSql()`.

```php
$total = $executor->count(
    $builder->select()->from('users')->where('active', 1)
);
// 42
```

---

## Write methods

### fetchAffected

Executes an INSERT, UPDATE, or DELETE query and returns the number of affected rows.

```php
$affected = $executor->fetchAffected(
    $builder->insert()->into('users')->column('name', 'Alice')
);
// 1

$affected = $executor->fetchAffected(
    $builder->update()->table('users')->column('active', 0)->where('id', 1)
);
// 1

$affected = $executor->fetchAffected(
    $builder->delete()->from('users')->where('active', 0)
);
// 5
```

---

## Escape hatch

### perform

Prepares and executes any query, returning the raw `PDOStatement`. Use this when the higher-level fetch methods do not cover your needs.

```php
$statement = $executor->perform(
    $builder->select()->from('users')->where('id', 1)
);

while ($row = $statement->fetch()) {
    // ...
}
```

`perform` always uses the write connection.
