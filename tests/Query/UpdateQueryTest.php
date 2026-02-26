<?php

namespace Georgeff\Database\Test\Query;

use Georgeff\Database\Query\UpdateQuery;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UpdateQueryTest extends TestCase
{
    private function make(): UpdateQuery
    {
        return new UpdateQuery('sqlite');
    }

    public function test_table(): void
    {
        $sql = $this->make()->table('users')->column('name', 'John')->toSql();

        $this->assertStringContainsString('UPDATE "users"', $sql);
    }

    public function test_column(): void
    {
        $query = $this->make()->table('users')->column('name', 'John');

        $this->assertStringContainsString('"name" = :name', $query->toSql());
        $this->assertSame(['name' => 'John'], $query->getBindings());
    }

    public function test_columns(): void
    {
        $query = $this->make()->table('users')->columns(['name' => 'John', 'email' => 'john@example.com']);

        $this->assertStringContainsString('"name" = :name', $query->toSql());
        $this->assertStringContainsString('"email" = :email', $query->toSql());
        $this->assertSame(['name' => 'John', 'email' => 'john@example.com'], $query->getBindings());
    }

    public function test_where(): void
    {
        $query = $this->make()->table('users')
            ->column('name', 'John')
            ->where('id', 1);

        $this->assertStringContainsString('WHERE', $query->toSql());
        $this->assertStringContainsString('id = :id_0', $query->toSql());
        $this->assertSame(['name' => 'John', 'id_0' => 1], $query->getBindings());
    }

    public function test_where_invalid_operator_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('[INVALID]');

        $this->make()->where('id', 1, 'INVALID');
    }
}
