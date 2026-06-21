<?php

namespace Georgeff\Database\Contract;

interface SelectInterface extends WhereInterface, QueryInterface
{
    /**
     * Set the table to query from
     */
    public function from(string $table): static;

    public function having(string $column, mixed $value, string $operator = '='): static;

    public function orHaving(string $column, mixed $value, string $operator = '='): static;

    /**
     * @param string[] $columns
     */
    public function groupBy(array $columns): static;

    public function orderBy(string $column, string $direction = 'DESC'): static;

    /**
     * Reset the order by clause on the select query
     */
    public function resetOrderBy(): static;

    public function limit(int $limit): static;

    public function offset(int $offset): static;

    public function setPaging(int $perPage, int $currentPage): static;

    public function getPerPage(): int;

    /**
     * Get current page
     */
    public function getPage(): int;

    public function join(string $join, string $spec, string $condition): static;

    public function leftJoin(string $spec, string $condition): static;

    public function rightJoin(string $spec, string $condition): static;

    public function innerJoin(string $spec, string $condition): static;

    public function distinct(bool $enable = true): static;

    public function toCountSql(): string;
}
