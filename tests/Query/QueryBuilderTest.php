<?php

namespace Georgeff\Database\Test\Query;

use Georgeff\Database\Query\DeleteQuery;
use Georgeff\Database\Query\InsertQuery;
use Georgeff\Database\Query\QueryBuilder;
use Georgeff\Database\Query\SelectQuery;
use Georgeff\Database\Query\UpdateQuery;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    private QueryBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new QueryBuilder('sqlite');
    }

    public function test_select_returns_select_query(): void
    {
        $this->assertInstanceOf(SelectQuery::class, $this->builder->select());
    }

    public function test_select_passes_columns(): void
    {
        $sql = $this->builder->select(['id', 'name'])->from('users')->toSql();

        $this->assertStringContainsString('id', $sql);
        $this->assertStringContainsString('name', $sql);
    }

    public function test_select_defaults_to_all_columns(): void
    {
        $sql = $this->builder->select()->from('users')->toSql();

        $this->assertStringContainsString('*', $sql);
    }

    public function test_insert_returns_insert_query(): void
    {
        $this->assertInstanceOf(InsertQuery::class, $this->builder->insert());
    }

    public function test_update_returns_update_query(): void
    {
        $this->assertInstanceOf(UpdateQuery::class, $this->builder->update());
    }

    public function test_delete_returns_delete_query(): void
    {
        $this->assertInstanceOf(DeleteQuery::class, $this->builder->delete());
    }

    public function test_each_call_returns_a_new_instance(): void
    {
        $this->assertNotSame($this->builder->select(), $this->builder->select());
        $this->assertNotSame($this->builder->insert(), $this->builder->insert());
        $this->assertNotSame($this->builder->update(), $this->builder->update());
        $this->assertNotSame($this->builder->delete(), $this->builder->delete());
    }
}
