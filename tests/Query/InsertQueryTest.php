<?php

namespace Georgeff\Database\Test\Query;

use Georgeff\Database\Query\InsertQuery;
use PHPUnit\Framework\TestCase;

class InsertQueryTest extends TestCase
{
    private function make(): InsertQuery
    {
        return new InsertQuery('sqlite');
    }

    public function test_into(): void
    {
        $sql = $this->make()->into('users')->column('name', 'John')->toSql();

        $this->assertStringContainsString('INSERT INTO "users"', $sql);
    }

    public function test_column(): void
    {
        $query = $this->make()->into('users')
            ->column('name', 'John')
            ->column('email', 'john@example.com');

        $this->assertStringContainsString('"name"', $query->toSql());
        $this->assertStringContainsString('"email"', $query->toSql());
        $this->assertSame(['name' => 'John', 'email' => 'john@example.com'], $query->getBindings());
    }

    public function test_add_row(): void
    {
        $query = $this->make()->into('users')
            ->addRow(['name' => 'John', 'email' => 'john@example.com']);

        $this->assertStringContainsString(':name', $query->toSql());
        $this->assertStringContainsString(':email', $query->toSql());
        $this->assertSame(['name' => 'John', 'email' => 'john@example.com'], $query->getBindings());
    }

    public function test_add_multiple_rows(): void
    {
        $query = $this->make()->into('users')
            ->addRow(['name' => 'John', 'email' => 'john@example.com'])
            ->addRow(['name' => 'Jane', 'email' => 'jane@example.com']);

        $this->assertStringContainsString(':name_0', $query->toSql());
        $this->assertStringContainsString(':name_1', $query->toSql());
        $this->assertSame([
            'name_0'  => 'John',
            'email_0' => 'john@example.com',
            'name_1'  => 'Jane',
            'email_1' => 'jane@example.com',
        ], $query->getBindings());
    }
}
