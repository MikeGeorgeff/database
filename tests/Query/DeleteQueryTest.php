<?php

namespace Georgeff\Database\Test\Query;

use Georgeff\Database\Query\DeleteQuery;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DeleteQueryTest extends TestCase
{
    private function make(): DeleteQuery
    {
        return new DeleteQuery('sqlite');
    }

    public function test_from(): void
    {
        $sql = $this->make()->from('users')->toSql();

        $this->assertStringContainsString('DELETE FROM "users"', $sql);
    }

    public function test_where(): void
    {
        $query = $this->make()->from('users')->where('id', 1);

        $this->assertStringContainsString('WHERE', $query->toSql());
        $this->assertStringContainsString('id = :id_0', $query->toSql());
        $this->assertSame(['id_0' => 1], $query->getBindings());
    }

    public function test_or_where(): void
    {
        $query = $this->make()->from('users')
            ->where('id', 1)
            ->orWhere('id', 2);

        $this->assertStringContainsString('OR', $query->toSql());
        $this->assertSame(['id_0' => 1, 'id_1' => 2], $query->getBindings());
    }

    public function test_where_in(): void
    {
        $query = $this->make()->from('users')->whereIn('id', [1, 2, 3]);

        $this->assertStringContainsString('id IN (', $query->toSql());
        $this->assertSame([1, 2, 3], array_values($query->getBindings()));
    }

    public function test_where_not_in(): void
    {
        $query = $this->make()->from('users')->whereNotIn('id', [1, 2]);

        $this->assertStringContainsString('id NOT IN (', $query->toSql());
        $this->assertSame([1, 2], array_values($query->getBindings()));
    }

    public function test_where_invalid_operator_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('[INVALID]');

        $this->make()->where('id', 1, 'INVALID');
    }
}
