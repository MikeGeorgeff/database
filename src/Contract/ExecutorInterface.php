<?php

namespace Georgeff\Database\Contract;

use PDOStatement;

interface ExecutorInterface
{
    /**
     * @return null|array<string, mixed>
     */
    public function fetchOne(SelectInterface $query): ?array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(SelectInterface $query): array;

    /**
     * Fetches the first column of rows as a sequential array
     *
     * @return array<int, mixed>
     */
    public function fetchCol(SelectInterface $query): array;

    /**
     * Return an associative array with first column as keys, second column as values
     *
     * @return array<array-key, mixed>
     */
    public function fetchPairs(SelectInterface $query): array;

    /**
     * Fetch the value of the first column of the first row
     */
    public function fetchValue(SelectInterface $query): mixed;

    /**
     * Performs a statement and returns the number of affected rows
     */
    public function fetchAffected(InsertInterface|UpdateInterface|DeleteInterface $query): int;

    /**
     * Performs a query after preparing the statement with bound values
     */
    public function perform(QueryInterface $query): PDOStatement;
}
