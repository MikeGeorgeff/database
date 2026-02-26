<?php

namespace Georgeff\Database\Contract;

interface TransactionManagerInterface
{
    /**
     * Execute a callback within a transaction
     *
     * Automatically:
     * - Begins transaction before executing the callback
     * - Commits the transaction if the callback completes successfully
     * - Rollsback the transaction if the callback throws an exception
     * - Re-throws the exception after rollback
     *
     * @param callable(): mixed $callback
     */
    public function execute(callable $callback): mixed;

    public function isTransacting(): bool;
}
