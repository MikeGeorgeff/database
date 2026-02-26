<?php

namespace Georgeff\Database\Contract;

interface UpdateInterface extends WhereInterface, QueryInterface
{
    public function table(string $table): static;

    public function column(string $name, mixed $value): static;

    /**
     * @param array<string, mixed> $columns
     */
    public function columns(array $columns): static;
}
