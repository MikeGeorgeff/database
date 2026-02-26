<?php

namespace Georgeff\Database\Query;

use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\Common\InsertInterface;
use Georgeff\Database\Contract\InsertInterface as InsertContract;

final class InsertQuery implements InsertContract
{
    private readonly InsertInterface $query;

    public function __construct(string $driver)
    {
        $this->query = new QueryFactory($driver)->newInsert();
    }

    public function into(string $table): static
    {
        $this->query->into($table);

        return $this;
    }

    public function column(string $name, mixed $value): static
    {
        $this->query->col($name, $value);

        return $this;
    }

    public function addRow(array $columns): static
    {
        $this->query->addRow($columns);

        return $this;
    }

    public function toSql(): string
    {
        return $this->query->getStatement();
    }

    public function getBindings(): array
    {
        return $this->query->getBindValues();
    }
}
