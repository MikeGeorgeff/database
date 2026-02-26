<?php

namespace Georgeff\Database\Contract;

interface QueryInterface
{
    /**
     * Get the query as raw sql
     */
    public function toSql(): string;

    /**
     * Get the values to bind to the query
     *
     * @return array<int|string, mixed>
     */
    public function getBindings(): array;
}
