<?php

namespace Georgeff\Database\Test\Query;

use Georgeff\Database\Query\SelectQuery;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SelectQueryTest extends TestCase
{
    private function make(array $columns = ['*']): SelectQuery
    {
        return new SelectQuery('sqlite', $columns);
    }

    public function test_selects_all_columns_by_default(): void
    {
        $sql = $this->make()->from('users')->toSql();

        $this->assertStringContainsString('SELECT', $sql);
        $this->assertStringContainsString('*', $sql);
        $this->assertStringContainsString('"users"', $sql);
    }

    public function test_selects_specific_columns(): void
    {
        $sql = $this->make(['id', 'name'])->from('users')->toSql();

        $this->assertStringContainsString('id', $sql);
        $this->assertStringContainsString('name', $sql);
    }

    public function test_where(): void
    {
        $query = $this->make()->from('users')->where('id', 1);

        $this->assertStringContainsString('id = :id_0', $query->toSql());
        $this->assertSame(['id_0' => 1], $query->getBindings());
    }

    public function test_where_with_operator(): void
    {
        $query = $this->make()->from('users')->where('age', 18, '>=');

        $this->assertStringContainsString('age >= :age_0', $query->toSql());
        $this->assertSame(['age_0' => 18], $query->getBindings());
    }

    public function test_or_where(): void
    {
        $query = $this->make()->from('users')
            ->where('status', 1)
            ->orWhere('role', 'admin');

        $this->assertStringContainsString('status = :status_0', $query->toSql());
        $this->assertStringContainsString('OR', $query->toSql());
        $this->assertStringContainsString('role = :role_1', $query->toSql());
        $this->assertSame(['status_0' => 1, 'role_1' => 'admin'], $query->getBindings());
    }

    public function test_where_in(): void
    {
        $query = $this->make()->from('users')->whereIn('status', [1, 2, 3]);

        $this->assertStringContainsString('status IN (', $query->toSql());
        $this->assertSame([1, 2, 3], array_values($query->getBindings()));
    }

    public function test_where_not_in(): void
    {
        $query = $this->make()->from('users')->whereNotIn('status', [1, 2]);

        $this->assertStringContainsString('status NOT IN (', $query->toSql());
        $this->assertSame([1, 2], array_values($query->getBindings()));
    }

    public function test_having(): void
    {
        $query = $this->make(['status', 'COUNT(*) as total'])
            ->from('users')
            ->groupBy(['status'])
            ->having('total', 5, '>');

        $this->assertStringContainsString('HAVING', $query->toSql());
        $this->assertStringContainsString('total > :total_0', $query->toSql());
        $this->assertSame(['total_0' => 5], $query->getBindings());
    }

    public function test_or_having(): void
    {
        $query = $this->make(['status', 'COUNT(*) as total'])
            ->from('users')
            ->groupBy(['status'])
            ->having('total', 5, '>')
            ->orHaving('total', 1, '<');

        $this->assertStringContainsString('OR', $query->toSql());
        $this->assertStringContainsString('total < :total_1', $query->toSql());
        $this->assertSame(['total_0' => 5, 'total_1' => 1], $query->getBindings());
    }

    public function test_group_by(): void
    {
        $sql = $this->make(['status', 'COUNT(*) as total'])
            ->from('users')
            ->groupBy(['status'])
            ->toSql();

        $this->assertStringContainsString('GROUP BY', $sql);
        $this->assertStringContainsString('status', $sql);
    }

    public function test_order_by_defaults_to_desc(): void
    {
        $sql = $this->make()->from('users')->orderBy('name')->toSql();

        $this->assertStringContainsString('ORDER BY', $sql);
        $this->assertStringContainsString('name DESC', $sql);
    }

    public function test_order_by_asc(): void
    {
        $sql = $this->make()->from('users')->orderBy('name', 'ASC')->toSql();

        $this->assertStringContainsString('name ASC', $sql);
    }

    public function test_order_by_invalid_direction_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->make()->from('users')->orderBy('name', 'INVALID');
    }

    public function test_limit(): void
    {
        $sql = $this->make()->from('users')->limit(10)->toSql();

        $this->assertStringContainsString('LIMIT 10', $sql);
    }

    public function test_offset(): void
    {
        $sql = $this->make()->from('users')->limit(10)->offset(20)->toSql();

        $this->assertStringContainsString('OFFSET 20', $sql);
    }

    public function test_set_paging(): void
    {
        $sql = $this->make()->from('users')->setPaging(10, 2)->toSql();

        $this->assertStringContainsString('LIMIT 10', $sql);
        $this->assertStringContainsString('OFFSET 10', $sql);
    }

    public function test_get_per_page(): void
    {
        $query = $this->make()->from('users')->setPaging(15, 1);

        $this->assertSame(15, $query->getPerPage());
    }

    public function test_get_page(): void
    {
        $query = $this->make()->from('users')->setPaging(10, 3);

        $this->assertSame(3, $query->getPage());
    }

    public function test_distinct(): void
    {
        $sql = $this->make()->from('users')->distinct()->toSql();

        $this->assertStringContainsString('SELECT DISTINCT', $sql);
    }

    public function test_distinct_can_be_disabled(): void
    {
        $sql = $this->make()->from('users')->distinct(false)->toSql();

        $this->assertStringNotContainsString('DISTINCT', $sql);
    }

    public function test_to_count_sql_resets_columns(): void
    {
        $sql = $this->make(['id', 'name'])->from('users')->toCountSql();

        $this->assertStringContainsString('COUNT(*)', $sql);
        $this->assertStringNotContainsString('id', $sql);
        $this->assertStringNotContainsString('name', $sql);
    }

    public function test_to_count_sql_resets_group_by(): void
    {
        $sql = $this->make(['status', 'COUNT(*) as total'])
            ->from('users')
            ->groupBy(['status'])
            ->toCountSql();

        $this->assertStringNotContainsString('GROUP BY', $sql);
    }

    public function test_to_count_sql_preserves_where(): void
    {
        $query = $this->make()->from('users')->where('active', 1);

        $this->assertStringContainsString('active = :active_0', $query->toCountSql());
    }

    public function test_to_count_sql_does_not_modify_original_query(): void
    {
        $query = $this->make(['id', 'name'])->from('users');
        $query->toCountSql();

        $this->assertStringContainsString('id', $query->toSql());
        $this->assertStringContainsString('name', $query->toSql());
    }

    public function test_where_invalid_operator_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('[INVALID]');
        $this->expectExceptionMessage('where');

        $this->make()->where('id', 1, 'INVALID');
    }

    public function test_or_where_invalid_operator_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('orWhere');

        $this->make()->orWhere('id', 1, 'INVALID');
    }

    public function test_having_invalid_operator_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('having');

        $this->make()->having('total', 5, 'INVALID');
    }

    public function test_bind_placeholder_sanitizes_table_qualified_column(): void
    {
        $query = $this->make()->from('users')->where('users.id', 1);

        $this->assertStringContainsString(':usersid_0', $query->toSql());
        $this->assertArrayHasKey('usersid_0', $query->getBindings());
    }

    public function test_multiple_where_clauses_have_unique_placeholders(): void
    {
        $query = $this->make()->from('users')
            ->where('id', 1)
            ->where('id', 2);

        $this->assertStringContainsString(':id_0', $query->toSql());
        $this->assertStringContainsString(':id_1', $query->toSql());
        $this->assertSame(['id_0' => 1, 'id_1' => 2], $query->getBindings());
    }
}
