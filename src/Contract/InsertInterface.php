<?php

namespace Georgeff\Database\Contract;

interface InsertInterface extends QueryInterface
{
    /**
     * Table to insert into
     */
    public function into(string $table): static;

    public function column(string $name, mixed $value): static;

    /**
     * @param array<string, mixed> $columns
     *
     * ['column' => 'value']
     */
    public function addRow(array $columns): static;
}
