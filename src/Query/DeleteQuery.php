<?php

namespace Georgeff\Database\Query;

use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\Common\WhereInterface;
use Aura\SqlQuery\Common\DeleteInterface;
use Georgeff\Database\Contract\DeleteInterface as DeleteContract;

final class DeleteQuery implements DeleteContract
{
    use HasWhereClauses;

    private readonly DeleteInterface $query;

    public function __construct(string $driver)
    {
        $this->query = new QueryFactory($driver)->newDelete();
    }

    private function getWhereQuery(): WhereInterface
    {
        return $this->query;
    }

    public function from(string $table): static
    {
        $this->query->from($table);

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
