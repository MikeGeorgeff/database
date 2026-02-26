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

    public function __construct(ConnectionManagerInterface $connectionManager)
    {
        $this->builder = new QueryBuilder($connectionManager->getDriverName());
        $this->transactionManager = new TransactionManager($connectionManager);
        $this->executor = new Executor($connectionManager);
    }

    /**
     * @param callable(): mixed $callback
     */
    public function transaction(callable $callback): mixed
    {
        return $this->transactionManager->execute($callback);
    }

    /**
     * @param string[] $columns
     */
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

    /**
     * @return null|array<string, mixed>
     */
    public function fetchOne(SelectInterface $query): ?array
    {
        return $this->executor->fetchOne($query);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(SelectInterface $query): array
    {
        return $this->executor->fetchAll($query);
    }

    /**
     * @return array<int, mixed>
     */
    public function fetchCol(SelectInterface $query): array
    {
        return $this->executor->fetchCol($query);
    }

    /**
     * Fetch the value of the first column of the first row
     */
    public function fetchValue(SelectInterface $query): mixed
    {
        return $this->executor->fetchValue($query);
    }

    /**
     * Performs a statement and returns the number of affected rows
     */
    public function fetchAffected(InsertInterface|UpdateInterface|DeleteInterface $query): int
    {
        return $this->executor->fetchAffected($query);
    }

    /**
     * Performs a query after preparing the statement with bound values
     */
    public function perform(QueryInterface $query): PDOStatement
    {
        return $this->executor->perform($query);
    }
}
