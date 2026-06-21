# Query Builder

`QueryBuilder` produces query objects for SELECT, INSERT, UPDATE, and DELETE statements. It does not execute queries — pass the resulting query object to an `Executor` or `DatabaseManager`.

> **Note:** The query builder interfaces (`SelectInterface`, `InsertInterface`, `UpdateInterface`, `DeleteInterface`, `WhereInterface`, `QueryInterface`, `QueryBuilderInterface`, `ExecutorInterface`) are consumption contracts — they describe what this package provides and are not designed to be implemented outside of it. Additive changes (new methods) may be introduced in minor releases without a major version bump. Implementing these interfaces in your own code is unsupported.

```php
use Georgeff\Database\Query\QueryBuilder;

$builder = new QueryBuilder('mysql'); // or 'sqlite', 'pgsql'
```

When using `DatabaseManager`, a `QueryBuilder` is wired up internally and its methods are available directly on the manager.

## SELECT

```php
$query = $builder->select()              // SELECT *
$query = $builder->select(['id', 'name']) // SELECT id, name
```

### from

```php
$query->from('users');
```

### WHERE clauses

```php
$query->where('status', 1);               // status = :status_0
$query->where('age', 18, '>=');           // age >= :age_0
$query->orWhere('role', 'admin');         // OR role = :role_1

$query->whereIn('status', [1, 2, 3]);     // status IN (...)
$query->whereNotIn('status', [4, 5]);     // status NOT IN (...)
```

Valid operators: `=`, `!=`, `<>`, `<`, `>`, `<=`, `>=`, `LIKE`, `NOT LIKE`, `ILIKE`, `NOT ILIKE`.

An `InvalidArgumentException` is thrown for invalid operators.

Table-qualified column names are supported. The dot is stripped from the bind placeholder automatically:

```php
$query->where('users.id', 1); // users.id = :usersid_0
```

### JOIN

```php
$query->join('INNER', 'orders', 'users.id = orders.user_id');
$query->join('LEFT',  'orders', 'users.id = orders.user_id');
$query->join('RIGHT', 'orders', 'users.id = orders.user_id');
```

Helper methods are available for each join type:

```php
$query->innerJoin('orders',   'users.id = orders.user_id');
$query->leftJoin('orders',    'users.id = orders.user_id');
$query->rightJoin('orders',   'users.id = orders.user_id');
```

The `$join` argument to `join()` is case-insensitive. An `InvalidArgumentException` is thrown for unsupported join types. Multiple joins can be chained.

### HAVING

```php
$query->having('total', 5, '>');     // HAVING total > :total_0
$query->orHaving('total', 1, '<');   // OR total < :total_1
```

### GROUP BY

```php
$query->groupBy(['status', 'role']);
```

### ORDER BY

```php
$query->orderBy('name');          // ORDER BY name DESC (default)
$query->orderBy('name', 'ASC');   // ORDER BY name ASC
```

An `InvalidArgumentException` is thrown if the direction is not `ASC` or `DESC`.

Multiple `orderBy()` calls stack — each call appends to the existing `ORDER BY` clause rather than replacing it:

```php
$query->orderBy('name', 'ASC')->orderBy('created_at', 'DESC');
// ORDER BY name ASC, created_at DESC
```

Use `resetOrderBy()` to clear all previously set ordering before applying a new one:

```php
$query->orderBy('name', 'ASC')->resetOrderBy()->orderBy('id', 'ASC');
// ORDER BY id ASC
```

### LIMIT and OFFSET

```php
$query->limit(10);
$query->offset(20);
```

### Pagination

`setPaging()` sets both `LIMIT` and `OFFSET` based on a per-page count and page number.

```php
$query->setPaging(15, 2);  // LIMIT 15 OFFSET 15

$query->getPerPage(); // 15
$query->getPage();    // 2
```

### DISTINCT

```php
$query->distinct();        // SELECT DISTINCT
$query->distinct(false);   // removes DISTINCT
```

### Count query

`toCountSql()` returns a version of the query with columns replaced by `COUNT(*) as total` and `GROUP BY` removed. The original query is not modified.

```php
$countSql = $query->toCountSql(); // SELECT COUNT(*) as total FROM "users" WHERE ...
$fullSql   = $query->toSql();     // original query unchanged
```

---

## INSERT

```php
$query = $builder->insert();

$query->into('users')
      ->column('name', 'Alice')
      ->column('email', 'alice@example.com');
```

### Bulk insert

Use `addRow()` to insert multiple rows in a single statement:

```php
$query->into('users')
      ->addRow(['name' => 'Alice', 'email' => 'alice@example.com'])
      ->addRow(['name' => 'Bob',   'email' => 'bob@example.com']);
```

---

## UPDATE

```php
$query = $builder->update();

$query->table('users')
      ->column('active', 0)
      ->where('last_login', '2020-01-01', '<');
```

### Setting multiple columns at once

```php
$query->columns([
    'first_name' => 'Alice',
    'last_name'  => 'Smith',
]);
```

UPDATE supports the same WHERE clause methods as SELECT (`where`, `orWhere`, `whereIn`, `whereNotIn`).

---

## DELETE

```php
$query = $builder->delete();

$query->from('users')
      ->where('active', 0);
```

DELETE supports the same WHERE clause methods as SELECT.

---

## Inspecting a query

All query types implement `QueryInterface`:

```php
$query->toSql();       // string — the SQL statement with placeholders
$query->getBindings(); // array  — the bound values keyed by placeholder
```
