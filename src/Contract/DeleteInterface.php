<?php

namespace Georgeff\Database\Contract;

interface DeleteInterface extends WhereInterface, QueryInterface
{
    public function from(string $table): static;
}
