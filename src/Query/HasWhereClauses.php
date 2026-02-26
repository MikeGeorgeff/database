<?php

namespace Georgeff\Database\Query;

use InvalidArgumentException;
use Aura\SqlQuery\Common\WhereInterface;

trait HasWhereClauses
{
    private const array VALID_OPERATORS = [
        '=', '!=', '<>', '<', '>', '<=', '>=',
        'LIKE', 'NOT LIKE', 'ILIKE', 'NOT ILIKE',
    ];

    /**
     * Incrementing index to add uniqueness to binds
     */
    private int $bindIndex = 0;

    abstract private function getWhereQuery(): WhereInterface;

    /**
     * @throws \InvalidArgumentException Invalid operator
     */
    public function where(string $column, mixed $value, string $operator = '='): static
    {
        $this->throwIfInvalidOperator($operator, __FUNCTION__);

        $placeholder = $this->getBindPlaceholder($column);

        $this->getWhereQuery()->where("{$column} {$operator} :{$placeholder}", [$placeholder => $value]);

        return $this;
    }

    /**
     * @throws \InvalidArgumentException Invalid operator
     */
    public function orWhere(string $column, mixed $value, string $operator = '='): static
    {
        $this->throwIfInvalidOperator($operator, __FUNCTION__);

        $placeholder = $this->getBindPlaceholder($column);

        $this->getWhereQuery()->orWhere("{$column} {$operator} :{$placeholder}", [$placeholder => $value]);

        return $this;
    }

    /**
     * @param array<int, mixed> $values
     */
    public function whereIn(string $column, array $values): static
    {
        $placeholder = $this->getBindPlaceholder($column);

        $this->getWhereQuery()->where("{$column} IN (:{$placeholder})", [$placeholder => $values]);

        return $this;
    }

    /**
     * @param array<int, mixed> $values
     */
    public function whereNotIn(string $column, array $values): static
    {
        $placeholder = $this->getBindPlaceholder($column);

        $this->getWhereQuery()->where("{$column} NOT IN (:{$placeholder})", [$placeholder => $values]);

        return $this;
    }

    private function throwIfInvalidOperator(string $operator, string $method): void
    {
        if (!in_array($operator, self::VALID_OPERATORS, true)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid operator [%s] in %s clause.  Valid Operators: %s',
                $operator,
                $method,
                implode(', ', self::VALID_OPERATORS)
            ));
        }
    }

    /**
     * Generate a unique bind placeholder and increment the bind index
     */
    private function getBindPlaceholder(string $column): string
    {
        $column = preg_replace('/[^a-zA-Z0-9]/', '', $column);

        $placeholder = "{$column}_{$this->bindIndex}";

        $this->bindIndex++;

        return $placeholder;
    }
}
