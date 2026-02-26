<?php

namespace Georgeff\Database\Test\Connection;

use Georgeff\Database\Connection\SqliteDriver;
use PHPUnit\Framework\TestCase;

class SqliteDriverTest extends TestCase
{
    public function test_defaults_to_in_memory_database(): void
    {
        $driver = new SqliteDriver();

        $this->assertSame('sqlite::memory:', $driver->getDsn());
    }

    public function test_uses_provided_database_path(): void
    {
        $driver = new SqliteDriver('/var/db/app.sqlite');

        $this->assertSame('sqlite:/var/db/app.sqlite', $driver->getDsn());
    }

    public function test_get_name(): void
    {
        $driver = new SqliteDriver();

        $this->assertSame('sqlite', $driver->getName());
    }

    public function test_username_is_null(): void
    {
        $driver = new SqliteDriver();

        $this->assertNull($driver->getUsername());
    }

    public function test_password_is_null(): void
    {
        $driver = new SqliteDriver();

        $this->assertNull($driver->getPassword());
    }

    public function test_has_no_read_replicas(): void
    {
        $driver = new SqliteDriver();

        $this->assertFalse($driver->hasReadReplicas());
        $this->assertSame([], $driver->getDsnForReadReplicas());
    }

    public function test_is_not_sticky(): void
    {
        $driver = new SqliteDriver();

        $this->assertFalse($driver->isSticky());
    }

    public function test_from_array_defaults_to_in_memory(): void
    {
        $driver = SqliteDriver::fromArray([]);

        $this->assertSame('sqlite::memory:', $driver->getDsn());
    }

    public function test_from_array_uses_database_key(): void
    {
        $driver = SqliteDriver::fromArray(['database' => '/var/db/app.sqlite']);

        $this->assertSame('sqlite:/var/db/app.sqlite', $driver->getDsn());
    }
}
