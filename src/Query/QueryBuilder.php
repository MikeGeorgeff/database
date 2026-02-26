<?php

namespace Georgeff\Database\Query;

use Georgeff\Database\Contract\SelectInterface;
use Georgeff\Database\Contract\InsertInterface;
use Georgeff\Database\Contract\UpdateInterface;
use Georgeff\Database\Contract\DeleteInterface;
use Georgeff\Database\Contract\QueryBuilderInterface;

final class QueryBuilder implements QueryBuilderInterface
{
    public function __construct(private readonly string $driver) {}

    /**
     * @param string[] $columns
     */
    public function select(array $columns = ['*']): SelectInterface
    {
        return new SelectQuery($this->driver, $columns);
    }

    public function insert(): InsertInterface
    {
        return new InsertQuery($this->driver);
    }

    public function update(): UpdateInterface
    {
        return new UpdateQuery($this->driver);
    }

    public function delete(): DeleteInterface
    {
        return new DeleteQuery($this->driver);
    }
}
