<?php

namespace Georgeff\Database\Test;

use PDOStatement;
use Aura\Sql\ExtendedPdoInterface;
use Georgeff\Database\DatabaseManager;
use Georgeff\Database\Contract\ConnectionManagerInterface;
use Georgeff\Database\Contract\InsertInterface;
use Georgeff\Database\Contract\UpdateInterface;
use Georgeff\Database\Contract\DeleteInterface;
use Georgeff\Database\Query\DeleteQuery;
use Georgeff\Database\Query\InsertQuery;
use Georgeff\Database\Query\SelectQuery;
use Georgeff\Database\Query\UpdateQuery;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class DatabaseManagerTest extends TestCase
{
    private function makeManager(): array
    {
        $connection = $this->createMock(ExtendedPdoInterface::class);

        $connectionManager = $this->createMock(ConnectionManagerInterface::class);
        $connectionManager->method('getDriverName')->willReturn('sqlite');
        $connectionManager->method('getWriteConnection')->willReturn($connection);
        $connectionManager->method('getReadConnection')->willReturn($connection);

        return [new DatabaseManager($connectionManager), $connection, $connectionManager];
    }

    public function test_transaction_executes_callback(): void
    {
        [$manager] = $this->makeManager();

        $called = false;

        $manager->transaction(function () use (&$called) {
            $called = true;
        });

        $this->assertTrue($called);
    }

    public function test_transaction_returns_callback_result(): void
    {
        [$manager] = $this->makeManager();

        $result = $manager->transaction(fn() => 'result');

        $this->assertSame('result', $result);
    }

    public function test_transaction_rethrows_exception(): void
    {
        [$manager] = $this->makeManager();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('something went wrong');

        $manager->transaction(fn() => throw new RuntimeException('something went wrong'));
    }

    public function test_select_returns_select_query(): void
    {
        [$manager] = $this->makeManager();

        $this->assertInstanceOf(SelectQuery::class, $manager->select());
    }

    public function test_select_passes_columns(): void
    {
        [$manager] = $this->makeManager();

        $sql = $manager->select(['id', 'name'])->from('users')->toSql();

        $this->assertStringContainsString('id', $sql);
        $this->assertStringContainsString('name', $sql);
    }

    public function test_select_defaults_to_all_columns(): void
    {
        [$manager] = $this->makeManager();

        $sql = $manager->select()->from('users')->toSql();

        $this->assertStringContainsString('*', $sql);
    }

    public function test_insert_returns_insert_query(): void
    {
        [$manager] = $this->makeManager();

        $this->assertInstanceOf(InsertQuery::class, $manager->insert());
    }

    public function test_update_returns_update_query(): void
    {
        [$manager] = $this->makeManager();

        $this->assertInstanceOf(UpdateQuery::class, $manager->update());
    }

    public function test_delete_returns_delete_query(): void
    {
        [$manager] = $this->makeManager();

        $this->assertInstanceOf(DeleteQuery::class, $manager->delete());
    }

    public function test_each_call_returns_a_new_query_instance(): void
    {
        [$manager] = $this->makeManager();

        $this->assertNotSame($manager->select(), $manager->select());
        $this->assertNotSame($manager->insert(), $manager->insert());
        $this->assertNotSame($manager->update(), $manager->update());
        $this->assertNotSame($manager->delete(), $manager->delete());
    }

    public function test_fetch_one_returns_row_when_found(): void
    {
        [$manager, $connection] = $this->makeManager();

        $connection->method('fetchOne')->willReturn(['id' => 1, 'name' => 'Alice']);

        $result = $manager->fetchOne($manager->select()->from('users'));

        $this->assertSame(['id' => 1, 'name' => 'Alice'], $result);
    }

    public function test_fetch_one_returns_null_when_not_found(): void
    {
        [$manager, $connection] = $this->makeManager();

        $connection->method('fetchOne')->willReturn(false);

        $this->assertNull($manager->fetchOne($manager->select()->from('users')));
    }

    public function test_fetch_all_returns_rows(): void
    {
        [$manager, $connection] = $this->makeManager();

        $rows = [['id' => 1], ['id' => 2]];
        $connection->method('fetchAll')->willReturn($rows);

        $this->assertSame($rows, $manager->fetchAll($manager->select()->from('users')));
    }

    public function test_fetch_col_returns_column_values(): void
    {
        [$manager, $connection] = $this->makeManager();

        $connection->method('fetchCol')->willReturn([1, 2, 3]);

        $this->assertSame([1, 2, 3], $manager->fetchCol($manager->select()->from('users')));
    }

    public function test_fetch_value_returns_scalar(): void
    {
        [$manager, $connection] = $this->makeManager();

        $connection->method('fetchValue')->willReturn(42);

        $this->assertSame(42, $manager->fetchValue($manager->select()->from('users')));
    }

    public function test_fetch_value_returns_null_when_not_found(): void
    {
        [$manager, $connection] = $this->makeManager();

        $connection->method('fetchValue')->willReturn(null);

        $this->assertNull($manager->fetchValue($manager->select()->from('users')));
    }

    public function test_count_returns_integer(): void
    {
        [$manager, $connection] = $this->makeManager();

        $connection->method('fetchValue')->willReturn(7);

        $this->assertSame(7, $manager->count($manager->select()->from('users')));
    }

    public function test_fetch_affected_returns_row_count(): void
    {
        [$manager, $connection] = $this->makeManager();

        $connection->method('fetchAffected')->willReturn(3);

        $query = $this->createMock(InsertInterface::class);
        $query->method('toSql')->willReturn('INSERT INTO "users"');
        $query->method('getBindings')->willReturn([]);

        $this->assertSame(3, $manager->fetchAffected($query));
    }

    public function test_fetch_affected_accepts_update_query(): void
    {
        [$manager, $connection] = $this->makeManager();

        $connection->method('fetchAffected')->willReturn(2);

        $query = $this->createMock(UpdateInterface::class);
        $query->method('toSql')->willReturn('UPDATE "users" SET');
        $query->method('getBindings')->willReturn([]);

        $this->assertSame(2, $manager->fetchAffected($query));
    }

    public function test_fetch_affected_accepts_delete_query(): void
    {
        [$manager, $connection] = $this->makeManager();

        $connection->method('fetchAffected')->willReturn(5);

        $query = $this->createMock(DeleteInterface::class);
        $query->method('toSql')->willReturn('DELETE FROM "users"');
        $query->method('getBindings')->willReturn([]);

        $this->assertSame(5, $manager->fetchAffected($query));
    }

    public function test_perform_returns_pdo_statement(): void
    {
        [$manager, $connection] = $this->makeManager();

        $statement = $this->createMock(PDOStatement::class);
        $connection->method('perform')->willReturn($statement);

        $this->assertSame($statement, $manager->perform($manager->select()->from('users')));
    }

    public function test_fetch_pairs_returns_key_value_pairs(): void
    {
        [$manager, $connection] = $this->makeManager();

        $connection->method('fetchPairs')->willReturn([1 => 'Alice', 2 => 'Bob']);

        $this->assertSame([1 => 'Alice', 2 => 'Bob'], $manager->fetchPairs($manager->select()->from('users')));
    }

    public function test_in_transaction_returns_false_when_not_in_transaction(): void
    {
        [$manager] = $this->makeManager();

        $this->assertFalse($manager->inTransaction());
    }

    public function test_in_transaction_returns_true_during_transaction(): void
    {
        [$manager] = $this->makeManager();

        $inTransaction = false;

        $manager->transaction(function () use ($manager, &$inTransaction) {
            $inTransaction = $manager->inTransaction();
        });

        $this->assertTrue($inTransaction);
    }

    public function test_last_insert_id_returns_id(): void
    {
        [$manager, $connection] = $this->makeManager();

        $connection->method('lastInsertId')->willReturn('42');

        $this->assertSame('42', $manager->lastInsertId());
    }

    public function test_last_insert_id_returns_null_when_driver_returns_false(): void
    {
        [$manager, $connection] = $this->makeManager();

        $connection->method('lastInsertId')->willReturn(false);

        $this->assertNull($manager->lastInsertId());
    }

    public function test_last_insert_id_passes_sequence_name(): void
    {
        [$manager, $connection] = $this->makeManager();

        $connection->expects($this->once())
            ->method('lastInsertId')
            ->with('users_id_seq')
            ->willReturn('5');

        $manager->lastInsertId('users_id_seq');
    }

    public function test_disconnect_delegates_to_connection_manager(): void
    {
        [$manager, , $connectionManager] = $this->makeManager();

        $connectionManager->expects($this->once())->method('disconnect');

        $manager->disconnect();
    }

    public function test_is_connected_returns_true_when_connected(): void
    {
        [$manager, , $connectionManager] = $this->makeManager();

        $connectionManager->method('isConnected')->willReturn(true);

        $this->assertTrue($manager->isConnected());
    }

    public function test_is_connected_returns_false_when_not_connected(): void
    {
        [$manager, , $connectionManager] = $this->makeManager();

        $connectionManager->method('isConnected')->willReturn(false);

        $this->assertFalse($manager->isConnected());
    }
}
