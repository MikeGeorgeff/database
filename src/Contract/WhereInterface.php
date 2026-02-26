<?php

namespace Georgeff\Database\Contract;

interface WhereInterface
{
    public function where(string $column, mixed $value, string $operator = '='): static;

    public function orWhere(string $column, mixed $value, string $operator = '='): static;

    /**
     * @param mixed[] $values
     */
    public function whereIn(string $column, array $values): static;

    /**
     * @param mixed[] $values
     */
    public function whereNotIn(string $column, array $values): static;
}
