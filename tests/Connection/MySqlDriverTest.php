<?php

namespace Georgeff\Database\Test\Connection;

use Georgeff\Database\Connection\MySqlDriver;
use PHPUnit\Framework\TestCase;

class MySqlDriverTest extends TestCase
{
    private function makeDriver(array $overrides = []): MySqlDriver
    {
        return new MySqlDriver(
            host: $overrides['host'] ?? 'localhost',
            database: $overrides['database'] ?? 'app',
            username: $overrides['username'] ?? 'root',
            password: $overrides['password'] ?? 'secret',
            port: $overrides['port'] ?? '3306',
            charset: $overrides['charset'] ?? 'utf8mb4',
            readHosts: $overrides['readHosts'] ?? [],
            sticky: $overrides['sticky'] ?? false,
        );
    }

    public function test_get_name(): void
    {
        $this->assertSame('mysql', $this->makeDriver()->getName());
    }

    public function test_get_dsn(): void
    {
        $driver = $this->makeDriver();

        $this->assertSame(
            'mysql:host=localhost;port=3306;dbname=app;charset=utf8mb4',
            $driver->getDsn()
        );
    }

    public function test_get_dsn_with_custom_port_and_charset(): void
    {
        $driver = $this->makeDriver(['port' => '3307', 'charset' => 'utf8']);

        $this->assertSame(
            'mysql:host=localhost;port=3307;dbname=app;charset=utf8',
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
                'mysql:host=replica1.db;port=3306;dbname=app;charset=utf8mb4',
                'mysql:host=replica2.db;port=3306;dbname=app;charset=utf8mb4',
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

    public function test_from_array(): void
    {
        $driver = MySqlDriver::fromArray([
            'hosts' => ['write' => 'db.primary'],
            'database' => 'app',
            'username' => 'root',
            'password' => 'secret',
        ]);

        $this->assertSame('mysql:host=db.primary;port=3306;dbname=app;charset=utf8mb4', $driver->getDsn());
        $this->assertSame('root', $driver->getUsername());
        $this->assertSame('secret', $driver->getPassword());
        $this->assertFalse($driver->hasReadReplicas());
        $this->assertFalse($driver->isSticky());
    }

    public function test_from_array_with_optional_fields(): void
    {
        $driver = MySqlDriver::fromArray([
            'hosts' => ['write' => 'db.primary', 'read' => ['db.replica']],
            'database' => 'app',
            'username' => 'root',
            'password' => 'secret',
            'port' => '3307',
            'charset' => 'utf8',
            'sticky' => true,
        ]);

        $this->assertSame('mysql:host=db.primary;port=3307;dbname=app;charset=utf8', $driver->getDsn());
        $this->assertTrue($driver->hasReadReplicas());
        $this->assertSame(['mysql:host=db.replica;port=3307;dbname=app;charset=utf8'], $driver->getDsnForReadReplicas());
        $this->assertTrue($driver->isSticky());
    }

}
