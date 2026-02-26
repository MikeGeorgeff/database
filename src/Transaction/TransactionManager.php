<?php

namespace Georgeff\Database\Transaction;

use Throwable;
use LogicException;
use Georgeff\Database\Contract\ConnectionManagerInterface;
use Georgeff\Database\Contract\TransactionManagerInterface;

final class TransactionManager implements TransactionManagerInterface
{
    private bool $transacting = false;

    public function __construct(private readonly ConnectionManagerInterface $connectionManager) {}

    public function execute(callable $callback): mixed
    {
        if ($this->isTransacting()) {
            throw new LogicException('Nested transactions are not supported');
        }

        $connection = $this->connectionManager->getWriteConnection();

        $connection->beginTransaction();

        $this->transacting = true;

        try {
            $result = $callback();

            $connection->commit();

            return $result;
        } catch (Throwable $e) {
            $connection->rollBack();

            throw $e;
        } finally {
            $this->transacting = false;
        }
    }

    public function isTransacting(): bool
    {
        return $this->transacting;
    }
}
