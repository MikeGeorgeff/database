<?php

namespace Georgeff\Database\Contract;

interface QueryBuilderInterface
{
    /**
     * @param string[] $columns
     */
    public function select(array $columns = ['*']): SelectInterface;

    public function insert(): InsertInterface;

    public function update(): UpdateInterface;

    public function delete(): DeleteInterface;
}
