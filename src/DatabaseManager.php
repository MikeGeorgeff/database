<?php

namespace Georgeff\Database;

use PDOStatement;
use Georgeff\Database\Execution\Executor;
use Georgeff\Database\Query\QueryBuilder;
use Georgeff\Database\Contract\QueryInterface;
use Georgeff\Database\Contract\SelectInterface;
use Georgeff\Database\Contract\InsertInterface;
use Georgeff\Database\Contract\UpdateInterface;
use Georgeff\Database\Contract\DeleteInterface;
use Georgeff\Database\Contract\ExecutorInterface;
use Georgeff\Database\Transaction\TransactionManager;
use Georgeff\Database\Contract\QueryBuilderInterface;
use Georgeff\Database\Contract\DatabaseManagerInterface;
use Georgeff\Database\Contract\ConnectionManagerInterface;
use Georgeff\Database\Contract\TransactionManagerInterface;

final class DatabaseManager implements DatabaseManagerInterface
{
    private readonly QueryBuilderInterface $builder;

    private readonly TransactionManagerInterface $transactionManager;

    private readonly ExecutorInterface $executor;

    public function __construct(private readonly ConnectionManagerInterface $connectionManager)
    {
        $this->builder = new QueryBuilder($connectionManager->getDriverName());
        $this->transactionManager = new TransactionManager($connectionManager);
        $this->executor = new Executor($connectionManager);
    }

    public function transaction(callable $callback): mixed
    {
        return $this->transactionManager->execute($callback);
    }

    public function inTransaction(): bool
    {
        return $this->transactionManager->isTransacting();
    }

    public function disconnect(): void
    {
        $this->connectionManager->disconnect();
    }

    public function isConnected(): bool
    {
        return $this->connectionManager->isConnected();
    }

    public function select(array $columns = ['*']): SelectInterface
    {
        return $this->builder->select($columns);
    }

    public function insert(): InsertInterface
    {
        return $this->builder->insert();
    }

    public function update(): UpdateInterface
    {
        return $this->builder->update();
    }

    public function delete(): DeleteInterface
    {
        return $this->builder->delete();
    }

    public function fetchOne(SelectInterface $query): ?array
    {
        return $this->executor->fetchOne($query);
    }

    public function fetchAll(SelectInterface $query): array
    {
        return $this->executor->fetchAll($query);
    }

    public function fetchCol(SelectInterface $query): array
    {
        return $this->executor->fetchCol($query);
    }

    public function fetchPairs(SelectInterface $query): array
    {
        return $this->executor->fetchPairs($query);
    }

    public function fetchValue(SelectInterface $query): mixed
    {
        return $this->executor->fetchValue($query);
    }

    public function fetchAffected(InsertInterface|UpdateInterface|DeleteInterface $query): int
    {
        return $this->executor->fetchAffected($query);
    }

    public function count(SelectInterface $query): int
    {
        return $this->executor->count($query);
    }

    public function perform(QueryInterface $query): PDOStatement
    {
        return $this->executor->perform($query);
    }

    public function lastInsertId(?string $name = null): ?string
    {
        $id = $this->connectionManager->getWriteConnection()->lastInsertId($name);

        return false === $id ? null : $id;
    }
}
