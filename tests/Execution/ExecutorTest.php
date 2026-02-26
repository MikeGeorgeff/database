<?php

namespace Georgeff\Database\Test\Execution;

use PDOStatement;
use Aura\Sql\ExtendedPdoInterface;
use Georgeff\Database\Execution\Executor;
use Georgeff\Database\Contract\QueryInterface;
use Georgeff\Database\Contract\SelectInterface;
use Georgeff\Database\Contract\InsertInterface;
use Georgeff\Database\Contract\UpdateInterface;
use Georgeff\Database\Contract\DeleteInterface;
use Georgeff\Database\Contract\ConnectionManagerInterface;
use PHPUnit\Framework\TestCase;

class ExecutorTest extends TestCase
{
    private function makeExecutor(): array
    {
        $readConnection = $this->createMock(ExtendedPdoInterface::class);
        $writeConnection = $this->createMock(ExtendedPdoInterface::class);

        $connectionManager = $this->createMock(ConnectionManagerInterface::class);
        $connectionManager->method('getReadConnection')->willReturn($readConnection);
        $connectionManager->method('getWriteConnection')->willReturn($writeConnection);

        return [new Executor($connectionManager), $connectionManager, $readConnection, $writeConnection];
    }

    private function makeSelectQuery(string $sql = 'SELECT * FROM "users"', array $bindings = []): SelectInterface
    {
        $query = $this->createMock(SelectInterface::class);
        $query->method('toSql')->willReturn($sql);
        $query->method('getBindings')->willReturn($bindings);

        return $query;
    }

    public function test_fetch_one_returns_row_when_found(): void
    {
        [$executor, , $readConnection] = $this->makeExecutor();

        $readConnection->method('fetchOne')->willReturn(['id' => 1, 'name' => 'Alice']);

        $result = $executor->fetchOne($this->makeSelectQuery());

        $this->assertSame(['id' => 1, 'name' => 'Alice'], $result);
    }

    public function test_fetch_one_returns_null_when_not_found(): void
    {
        [$executor, , $readConnection] = $this->makeExecutor();

        $readConnection->method('fetchOne')->willReturn(false);

        $this->assertNull($executor->fetchOne($this->makeSelectQuery()));
    }

    public function test_fetch_one_uses_read_connection(): void
    {
        [$executor, $connectionManager, $readConnection] = $this->makeExecutor();

        $readConnection->method('fetchOne')->willReturn(false);
        $connectionManager->expects($this->once())->method('getReadConnection');
        $connectionManager->expects($this->never())->method('getWriteConnection');

        $executor->fetchOne($this->makeSelectQuery());
    }

    public function test_fetch_one_passes_sql_and_bindings(): void
    {
        [$executor, , $readConnection] = $this->makeExecutor();

        $readConnection->expects($this->once())
            ->method('fetchOne')
            ->with('SELECT * FROM "users"', ['id_0' => 1])
            ->willReturn(false);

        $executor->fetchOne($this->makeSelectQuery('SELECT * FROM "users"', ['id_0' => 1]));
    }

    public function test_fetch_all_returns_rows(): void
    {
        [$executor, , $readConnection] = $this->makeExecutor();

        $rows = [['id' => 1, 'name' => 'Alice'], ['id' => 2, 'name' => 'Bob']];
        $readConnection->method('fetchAll')->willReturn($rows);

        $this->assertSame($rows, $executor->fetchAll($this->makeSelectQuery()));
    }

    public function test_fetch_all_returns_empty_array_when_no_results(): void
    {
        [$executor, , $readConnection] = $this->makeExecutor();

        $readConnection->method('fetchAll')->willReturn([]);

        $this->assertSame([], $executor->fetchAll($this->makeSelectQuery()));
    }

    public function test_fetch_all_uses_read_connection(): void
    {
        [$executor, $connectionManager, $readConnection] = $this->makeExecutor();

        $readConnection->method('fetchAll')->willReturn([]);
        $connectionManager->expects($this->once())->method('getReadConnection');
        $connectionManager->expects($this->never())->method('getWriteConnection');

        $executor->fetchAll($this->makeSelectQuery());
    }

    public function test_fetch_col_returns_column_values(): void
    {
        [$executor, , $readConnection] = $this->makeExecutor();

        $readConnection->method('fetchCol')->willReturn([1, 2, 3]);

        $this->assertSame([1, 2, 3], $executor->fetchCol($this->makeSelectQuery()));
    }

    public function test_fetch_col_returns_empty_array_when_no_results(): void
    {
        [$executor, , $readConnection] = $this->makeExecutor();

        $readConnection->method('fetchCol')->willReturn([]);

        $this->assertSame([], $executor->fetchCol($this->makeSelectQuery()));
    }

    public function test_fetch_col_uses_read_connection(): void
    {
        [$executor, $connectionManager, $readConnection] = $this->makeExecutor();

        $readConnection->method('fetchCol')->willReturn([]);
        $connectionManager->expects($this->once())->method('getReadConnection');
        $connectionManager->expects($this->never())->method('getWriteConnection');

        $executor->fetchCol($this->makeSelectQuery());
    }

    public function test_fetch_pairs_returns_key_value_pairs(): void
    {
        [$executor, , $readConnection] = $this->makeExecutor();

        $readConnection->method('fetchPairs')->willReturn([1 => 'Alice', 2 => 'Bob']);

        $this->assertSame([1 => 'Alice', 2 => 'Bob'], $executor->fetchPairs($this->makeSelectQuery()));
    }

    public function test_fetch_pairs_returns_empty_array_when_no_results(): void
    {
        [$executor, , $readConnection] = $this->makeExecutor();

        $readConnection->method('fetchPairs')->willReturn([]);

        $this->assertSame([], $executor->fetchPairs($this->makeSelectQuery()));
    }

    public function test_fetch_pairs_uses_read_connection(): void
    {
        [$executor, $connectionManager, $readConnection] = $this->makeExecutor();

        $readConnection->method('fetchPairs')->willReturn([]);
        $connectionManager->expects($this->once())->method('getReadConnection');
        $connectionManager->expects($this->never())->method('getWriteConnection');

        $executor->fetchPairs($this->makeSelectQuery());
    }

    public function test_fetch_pairs_passes_sql_and_bindings(): void
    {
        [$executor, , $readConnection] = $this->makeExecutor();

        $readConnection->expects($this->once())
            ->method('fetchPairs')
            ->with('SELECT "id", "name" FROM "users"', ['active_0' => 1])
            ->willReturn([]);

        $executor->fetchPairs($this->makeSelectQuery('SELECT "id", "name" FROM "users"', ['active_0' => 1]));
    }

    public function test_fetch_value_returns_scalar(): void
    {
        [$executor, , $readConnection] = $this->makeExecutor();

        $readConnection->method('fetchValue')->willReturn(42);

        $this->assertSame(42, $executor->fetchValue($this->makeSelectQuery()));
    }

    public function test_fetch_value_returns_null_when_not_found(): void
    {
        [$executor, , $readConnection] = $this->makeExecutor();

        $readConnection->method('fetchValue')->willReturn(null);

        $this->assertNull($executor->fetchValue($this->makeSelectQuery()));
    }

    public function test_fetch_value_uses_read_connection(): void
    {
        [$executor, $connectionManager, $readConnection] = $this->makeExecutor();

        $readConnection->method('fetchValue')->willReturn(null);
        $connectionManager->expects($this->once())->method('getReadConnection');
        $connectionManager->expects($this->never())->method('getWriteConnection');

        $executor->fetchValue($this->makeSelectQuery());
    }

    public function test_fetch_affected_returns_row_count(): void
    {
        [$executor, , , $writeConnection] = $this->makeExecutor();

        $writeConnection->method('fetchAffected')->willReturn(3);

        $query = $this->createMock(InsertInterface::class);
        $query->method('toSql')->willReturn('INSERT INTO "users"');
        $query->method('getBindings')->willReturn([]);

        $this->assertSame(3, $executor->fetchAffected($query));
    }

    public function test_fetch_affected_uses_write_connection(): void
    {
        [$executor, $connectionManager, , $writeConnection] = $this->makeExecutor();

        $writeConnection->method('fetchAffected')->willReturn(0);
        $connectionManager->expects($this->never())->method('getReadConnection');
        $connectionManager->expects($this->once())->method('getWriteConnection');

        $query = $this->createMock(UpdateInterface::class);
        $query->method('toSql')->willReturn('UPDATE "users" SET');
        $query->method('getBindings')->willReturn([]);

        $executor->fetchAffected($query);
    }

    public function test_fetch_affected_accepts_insert_query(): void
    {
        [$executor, , , $writeConnection] = $this->makeExecutor();

        $writeConnection->method('fetchAffected')->willReturn(1);

        $query = $this->createMock(InsertInterface::class);
        $query->method('toSql')->willReturn('INSERT INTO "users"');
        $query->method('getBindings')->willReturn([]);

        $this->assertSame(1, $executor->fetchAffected($query));
    }

    public function test_fetch_affected_accepts_update_query(): void
    {
        [$executor, , , $writeConnection] = $this->makeExecutor();

        $writeConnection->method('fetchAffected')->willReturn(2);

        $query = $this->createMock(UpdateInterface::class);
        $query->method('toSql')->willReturn('UPDATE "users" SET');
        $query->method('getBindings')->willReturn([]);

        $this->assertSame(2, $executor->fetchAffected($query));
    }

    public function test_fetch_affected_accepts_delete_query(): void
    {
        [$executor, , , $writeConnection] = $this->makeExecutor();

        $writeConnection->method('fetchAffected')->willReturn(5);

        $query = $this->createMock(DeleteInterface::class);
        $query->method('toSql')->willReturn('DELETE FROM "users"');
        $query->method('getBindings')->willReturn([]);

        $this->assertSame(5, $executor->fetchAffected($query));
    }

    public function test_perform_returns_pdo_statement(): void
    {
        [$executor, , , $writeConnection] = $this->makeExecutor();

        $statement = $this->createMock(PDOStatement::class);
        $writeConnection->method('perform')->willReturn($statement);

        $query = $this->createMock(QueryInterface::class);
        $query->method('toSql')->willReturn('SELECT 1');
        $query->method('getBindings')->willReturn([]);

        $this->assertSame($statement, $executor->perform($query));
    }

    public function test_perform_uses_write_connection(): void
    {
        [$executor, $connectionManager, , $writeConnection] = $this->makeExecutor();

        $writeConnection->method('perform')->willReturn($this->createMock(PDOStatement::class));
        $connectionManager->expects($this->never())->method('getReadConnection');
        $connectionManager->expects($this->once())->method('getWriteConnection');

        $query = $this->createMock(QueryInterface::class);
        $query->method('toSql')->willReturn('SELECT 1');
        $query->method('getBindings')->willReturn([]);

        $executor->perform($query);
    }

    public function test_perform_passes_sql_and_bindings(): void
    {
        [$executor, , , $writeConnection] = $this->makeExecutor();

        $writeConnection->expects($this->once())
            ->method('perform')
            ->with('DELETE FROM "users"', ['id_0' => 1])
            ->willReturn($this->createMock(PDOStatement::class));

        $query = $this->createMock(QueryInterface::class);
        $query->method('toSql')->willReturn('DELETE FROM "users"');
        $query->method('getBindings')->willReturn(['id_0' => 1]);

        $executor->perform($query);
    }
}
