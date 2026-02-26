<?php

namespace Georgeff\Database\Test\Connection;

use Georgeff\Database\Connection\PgsqlDriver;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PgsqlDriverTest extends TestCase
{
    private function makeDriver(array $overrides = []): PgsqlDriver
    {
        return new PgsqlDriver(
            host: $overrides['host'] ?? 'localhost',
            database: $overrides['database'] ?? 'app',
            username: $overrides['username'] ?? 'postgres',
            password: $overrides['password'] ?? 'secret',
            port: $overrides['port'] ?? '5432',
            schema: $overrides['schema'] ?? 'public',
            sslmode: $overrides['sslmode'] ?? 'prefer',
            readHosts: $overrides['readHosts'] ?? [],
            sticky: $overrides['sticky'] ?? false,
        );
    }

    public function test_get_name(): void
    {
        $this->assertSame('pgsql', $this->makeDriver()->getName());
    }

    public function test_get_dsn(): void
    {
        $driver = $this->makeDriver();

        $this->assertSame(
            "pgsql:host=localhost;port=5432;dbname=app;sslmode=prefer;options='--search_path=public'",
            $driver->getDsn()
        );
    }

    public function test_get_dsn_with_custom_options(): void
    {
        $driver = $this->makeDriver([
            'host' => 'db.primary',
            'port' => '5433',
            'schema' => 'tenant_1',
            'sslmode' => 'require',
        ]);

        $this->assertSame(
            "pgsql:host=db.primary;port=5433;dbname=app;sslmode=require;options='--search_path=tenant_1'",
            $driver->getDsn()
        );
    }

    public function test_get_username_and_password(): void
    {
        $driver = $this->makeDriver(['username' => 'mike', 'password' => 'hunter2']);

        $this->assertSame('mike', $driver->getUsername());
        $this->assertSame('hunter2', $driver->getPassword());
    }

    public function test_has_no_read_replicas_by_default(): void
    {
        $driver = $this->makeDriver();

        $this->assertFalse($driver->hasReadReplicas());
        $this->assertSame([], $driver->getDsnForReadReplicas());
    }

    public function test_has_read_replicas(): void
    {
        $driver = $this->makeDriver(['readHosts' => ['replica1.db', 'replica2.db']]);

        $this->assertTrue($driver->hasReadReplicas());
        $this->assertSame(
            [
                "pgsql:host=replica1.db;port=5432;dbname=app;sslmode=prefer;options='--search_path=public'",
                "pgsql:host=replica2.db;port=5432;dbname=app;sslmode=prefer;options='--search_path=public'",
            ],
            $driver->getDsnForReadReplicas()
        );
    }

    public function test_is_not_sticky_by_default(): void
    {
        $this->assertFalse($this->makeDriver()->isSticky());
    }

    public function test_is_sticky(): void
    {
        $this->assertTrue($this->makeDriver(['sticky' => true])->isSticky());
    }

    public function test_invalid_schema_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('[bad schema!]');

        $this->makeDriver(['schema' => 'bad schema!']);
    }

    public function test_schema_with_dollar_sign_is_valid(): void
    {
        $driver = $this->makeDriver(['schema' => 'tenant$1']);

        $this->assertStringContainsString('tenant$1', $driver->getDsn());
    }

    public function test_from_array(): void
    {
        $driver = PgsqlDriver::fromArray([
            'hosts' => ['write' => 'db.primary'],
            'database' => 'app',
            'username' => 'postgres',
            'password' => 'secret',
        ]);

        $this->assertSame(
            "pgsql:host=db.primary;port=5432;dbname=app;sslmode=prefer;options='--search_path=public'",
            $driver->getDsn()
        );
        $this->assertSame('postgres', $driver->getUsername());
        $this->assertSame('secret', $driver->getPassword());
        $this->assertFalse($driver->hasReadReplicas());
        $this->assertFalse($driver->isSticky());
    }

    public function test_from_array_with_optional_fields(): void
    {
        $driver = PgsqlDriver::fromArray([
            'hosts' => ['write' => 'db.primary', 'read' => ['db.replica']],
            'database' => 'app',
            'username' => 'postgres',
            'password' => 'secret',
            'port' => '5433',
            'schema' => 'myschema',
            'sslmode' => 'require',
            'sticky' => true,
        ]);

        $this->assertSame(
            "pgsql:host=db.primary;port=5433;dbname=app;sslmode=require;options='--search_path=myschema'",
            $driver->getDsn()
        );
        $this->assertTrue($driver->hasReadReplicas());
        $this->assertSame(
            ["pgsql:host=db.replica;port=5433;dbname=app;sslmode=require;options='--search_path=myschema'"],
            $driver->getDsnForReadReplicas()
        );
        $this->assertTrue($driver->isSticky());
    }

}
