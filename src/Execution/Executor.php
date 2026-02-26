<?php

namespace Georgeff\Database\Execution;

use PDOStatement;
use Georgeff\Database\Contract\QueryInterface;
use Georgeff\Database\Contract\SelectInterface;
use Georgeff\Database\Contract\InsertInterface;
use Georgeff\Database\Contract\UpdateInterface;
use Georgeff\Database\Contract\DeleteInterface;
use Georgeff\Database\Contract\ExecutorInterface;
use Georgeff\Database\Contract\ConnectionManagerInterface;

final class Executor implements ExecutorInterface
{
    public function __construct(private readonly ConnectionManagerInterface $connectionManager) {}

    public function fetchOne(SelectInterface $query): ?array
    {
        $pdo = $this->connectionManager->getReadConnection();

        /** @var array<string, mixed>|false $result */
        $result = $pdo->fetchOne($query->toSql(), $query->getBindings());

        if (false === $result) {
            $result = null;
        }

        return $result;
    }

    public function fetchAll(SelectInterface $query): array
    {
        $pdo = $this->connectionManager->getReadConnection();

        /** @var array<int, array<string, mixed>> $result */
        $result = $pdo->fetchAll($query->toSql(), $query->getBindings());

        return $result;
    }

    public function fetchCol(SelectInterface $query): array
    {
        $pdo = $this->connectionManager->getReadConnection();

        /** @var array<int, mixed> $result */
        $result = $pdo->fetchCol($query->toSql(), $query->getBindings());

        return $result;
    }

    public function fetchPairs(SelectInterface $query): array
    {
        $pdo = $this->connectionManager->getReadConnection();

        return $pdo->fetchPairs($query->toSql(), $query->getBindings());
    }

    public function fetchValue(SelectInterface $query): mixed
    {
        $pdo = $this->connectionManager->getReadConnection();

        return $pdo->fetchValue($query->toSql(), $query->getBindings());
    }

    public function fetchAffected(InsertInterface|UpdateInterface|DeleteInterface $query): int
    {
        $pdo = $this->connectionManager->getWriteConnection();

        return $pdo->fetchAffected($query->toSql(), $query->getBindings());
    }

    public function perform(QueryInterface $query): PDOStatement
    {
        $pdo = $this->connectionManager->getWriteConnection();

        return $pdo->perform($query->toSql(), $query->getBindings());
    }
}
