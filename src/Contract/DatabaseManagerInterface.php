<?php

namespace Georgeff\Database\Contract;

interface DatabaseManagerInterface extends QueryBuilderInterface, ExecutorInterface
{
    /**
     * @param callable(): mixed $callback
     */
    public function transaction(callable $callback): mixed;

    public function inTransaction(): bool;

    public function lastInsertId(?string $name = null): ?string;

    public function disconnect(): void;

    public function isConnected(): bool;
}
