<?php

namespace Georgeff\Database\Contract;

interface DatabaseManagerInterface extends QueryBuilderInterface, ExecutorInterface
{
    /**
     * @param callable(): mixed $callback
     */
    public function transaction(callable $callback): mixed;
}
