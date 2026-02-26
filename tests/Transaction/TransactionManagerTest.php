<?php

namespace Georgeff\Database\Test\Transaction;

use Aura\Sql\ExtendedPdoInterface;
use Georgeff\Database\Contract\ConnectionManagerInterface;
use Georgeff\Database\Transaction\TransactionManager;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TransactionManagerTest extends TestCase
{
    private function makeManager(): array
    {
        $connection = $this->createMock(ExtendedPdoInterface::class);
        $connectionManager = $this->createMock(ConnectionManagerInterface::class);
        $connectionManager->method('getWriteConnection')->willReturn($connection);

        return [new TransactionManager($connectionManager), $connection];
    }

    public function test_is_not_transacting_initially(): void
    {
        [$manager] = $this->makeManager();

        $this->assertFalse($manager->isTransacting());
    }

    public function test_is_transacting_during_execution(): void
    {
        [$manager] = $this->makeManager();

        $transactingDuringExecution = false;

        $manager->execute(function () use ($manager, &$transactingDuringExecution) {
            $transactingDuringExecution = $manager->isTransacting();
        });

        $this->assertTrue($transactingDuringExecution);
    }

    public function test_is_not_transacting_after_successful_execution(): void
    {
        [$manager] = $this->makeManager();

        $manager->execute(fn() => null);

        $this->assertFalse($manager->isTransacting());
    }

    public function test_is_not_transacting_after_failed_execution(): void
    {
        [$manager] = $this->makeManager();

        try {
            $manager->execute(fn() => throw new RuntimeException());
        } catch (RuntimeException) {
        }

        $this->assertFalse($manager->isTransacting());
    }

    public function test_execute_begins_and_commits_transaction(): void
    {
        [$manager, $connection] = $this->makeManager();

        $connection->expects($this->once())->method('beginTransaction');
        $connection->expects($this->once())->method('commit');
        $connection->expects($this->never())->method('rollBack');

        $manager->execute(fn() => null);
    }

    public function test_execute_returns_callback_result(): void
    {
        [$manager] = $this->makeManager();

        $result = $manager->execute(fn() => 'result');

        $this->assertSame('result', $result);
    }

    public function test_execute_returns_null_from_callback(): void
    {
        [$manager] = $this->makeManager();

        $this->assertNull($manager->execute(fn() => null));
    }

    public function test_execute_rolls_back_on_exception(): void
    {
        [$manager, $connection] = $this->makeManager();

        $connection->expects($this->once())->method('beginTransaction');
        $connection->expects($this->never())->method('commit');
        $connection->expects($this->once())->method('rollBack');

        try {
            $manager->execute(fn() => throw new RuntimeException());
        } catch (RuntimeException) {
        }
    }

    public function test_execute_rethrows_exception_after_rollback(): void
    {
        [$manager] = $this->makeManager();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('something went wrong');

        $manager->execute(fn() => throw new RuntimeException('something went wrong'));
    }

    public function test_nested_transaction_throws_logic_exception(): void
    {
        [$manager] = $this->makeManager();

        $this->expectException(LogicException::class);

        $manager->execute(function () use ($manager) {
            $manager->execute(fn() => null);
        });
    }

    public function test_nested_transaction_does_not_begin_inner_transaction(): void
    {
        [$manager, $connection] = $this->makeManager();

        $connection->expects($this->once())->method('beginTransaction');
        $connection->expects($this->never())->method('commit');
        $connection->expects($this->once())->method('rollBack');

        try {
            $manager->execute(function () use ($manager) {
                $manager->execute(fn() => null);
            });
        } catch (LogicException) {
        }
    }

    public function test_execute_uses_write_connection(): void
    {
        $connection = $this->createMock(ExtendedPdoInterface::class);
        $connectionManager = $this->createMock(ConnectionManagerInterface::class);
        $connectionManager->expects($this->once())->method('getWriteConnection')->willReturn($connection);

        $manager = new TransactionManager($connectionManager);

        $manager->execute(fn() => null);
    }
}
