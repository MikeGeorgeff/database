<?php

namespace Georgeff\Database\Query;

use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\Common\WhereInterface;
use Aura\SqlQuery\Common\UpdateInterface;
use Georgeff\Database\Contract\UpdateInterface as UpdateContract;

final class UpdateQuery implements UpdateContract
{
    use HasWhereClauses;

    private readonly UpdateInterface $query;

    public function __construct(string $driver)
    {
        $this->query = new QueryFactory($driver)->newUpdate();
    }

    private function getWhereQuery(): WhereInterface
    {
        return $this->query;
    }

    public function table(string $table): static
    {
        $this->query->table($table);

        return $this;
    }

    public function column(string $name, mixed $value): static
    {
        $this->query->col($name, $value);

        return $this;
    }

    public function columns(array $columns): static
    {
        $this->query->cols($columns);

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
