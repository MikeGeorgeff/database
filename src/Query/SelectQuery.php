<?php

namespace Georgeff\Database\Query;

use InvalidArgumentException;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\Common\WhereInterface;
use Aura\SqlQuery\Common\SelectInterface;
use Georgeff\Database\Contract\SelectInterface as SelectContract;

final class SelectQuery implements SelectContract
{
    use HasWhereClauses;

    private readonly SelectInterface $query;

    /**
     * @param string[] $columns
     */
    public function __construct(string $driver, array $columns = ['*'])
    {
        $this->query = new QueryFactory($driver)->newSelect()->cols($columns);
    }

    /**
     * Set the table to query from
     */
    public function from(string $table): static
    {
        $this->query->from($table);

        return $this;
    }

    public function having(string $column, mixed $value, string $operator = '='): static
    {
        $this->throwIfInvalidOperator($operator, __FUNCTION__);

        $placeholder = $this->getBindPlaceholder($column);

        $this->query->having("{$column} {$operator} :{$placeholder}", [$placeholder => $value]);

        return $this;
    }

    public function orHaving(string $column, mixed $value, string $operator = '='): static
    {
        $this->throwIfInvalidOperator($operator, __FUNCTION__);

        $placeholder = $this->getBindPlaceholder($column);

        $this->query->orHaving("{$column} {$operator} :{$placeholder}", [$placeholder => $value]);

        return $this;
    }

    public function join(string $join, string $spec, string $condition): static
    {
        $join = strtoupper($join);

        if (!in_array($join, ['LEFT', 'INNER', 'RIGHT'], true)) {
            throw new InvalidArgumentException("Invalid join type [$join]. Join type must be LEFT, RIGHT or INNER");
        }

        $this->query->join($join, $spec, $condition);

        return $this;
    }

    public function leftJoin(string $spec, string $condition): static
    {
        return $this->join('LEFT', $spec, $condition);
    }

    public function rightJoin(string $spec, string $condition): static
    {
        return $this->join('RIGHT', $spec, $condition);
    }

    public function innerJoin(string $spec, string $condition): static
    {
        return $this->join('INNER', $spec, $condition);
    }

    public function groupBy(array $columns): static
    {
        $this->query->groupBy($columns);

        return $this;
    }

    public function orderBy(string $column, string $direction = 'DESC'): static
    {
        $direction = strtoupper($direction);

        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            throw new InvalidArgumentException('Order by direction must be either ASC or DESC');
        }

        $this->query->orderBy(["{$column} {$direction}"]);

        return $this;
    }

    public function resetOrderBy(): static
    {
        $this->query->resetOrderBy();

        return $this;
    }

    public function limit(int $limit): static
    {
        $this->query->limit($limit);

        return $this;
    }

    public function offset(int $offset): static
    {
        $this->query->offset($offset);

        return $this;
    }

    public function setPaging(int $perPage, int $currentPage): static
    {
        $this->query->setPaging($perPage)->page($currentPage);

        return $this;
    }

    public function getPerPage(): int
    {
        return $this->query->getPaging();
    }

    public function getPage(): int
    {
        return $this->query->getPage();
    }

    public function distinct(bool $enable = true): static
    {
        $this->query->distinct($enable);

        return $this;
    }

    public function toCountSql(): string
    {
        $q = clone $this->query;

        return $q->resetCols()
                 ->resetGroupBy()
                 ->cols(['COUNT(*) as total'])
                 ->getStatement();
    }

    public function toSql(): string
    {
        return $this->query->getStatement();
    }

    public function getBindings(): array
    {
        return $this->query->getBindValues();
    }

    private function getWhereQuery(): WhereInterface
    {
        return $this->query;
    }
}
