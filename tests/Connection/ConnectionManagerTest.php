<?php

namespace Georgeff\Database\Test\Connection;

use Aura\Sql\ExtendedPdoInterface;
use Georgeff\Database\Connection\ConnectionManager;
use Georgeff\Database\Connection\DriverInterface;
use PDO;
use PHPUnit\Framework\TestCase;

class ConnectionManagerTest extends TestCase
{
    private function makeDriver(
        string $dsn = 'sqlite::memory:',
        array $readDsns = [],
        bool $sticky = false,
        string $name = 'sqlite'
    ): DriverInterface {
        return new class($dsn, $readDsns, $sticky, $name) implements DriverInterface {
            public function __construct(
                private readonly string $dsn,
                private readonly array $readDsns,
                private readonly bool $sticky,
                private readonly string $name,
            ) {}

            public function getName(): string { return $this->name; }
            public function getDsn(): string { return $this->dsn; }
            public function getUsername(): ?string { return null; }
            public function getPassword(): ?string { return null; }
            public function hasReadReplicas(): bool { return [] !== $this->readDsns; }
            public function isSticky(): bool { return $this->sticky; }
            public function getDsnForReadReplicas(): array { return $this->readDsns; }

            public function getPdoOptions(): array
            {
                return [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ];
            }
        };
    }

    public function test_is_not_connected_initially(): void
    {
        $manager = new ConnectionManager($this->makeDriver());

        $this->assertFalse($manager->isConnected());
    }

    public function test_get_write_connection_returns_extended_pdo_interface(): void
    {
        $manager = new ConnectionManager($this->makeDriver());

        $this->assertInstanceOf(ExtendedPdoInterface::class, $manager->getWriteConnection());
    }

    public function test_is_connected_after_get_write_connection(): void
    {
        $manager = new ConnectionManager($this->makeDriver());
        $manager->getWriteConnection();

        $this->assertTrue($manager->isConnected());
    }

    public function test_is_connected_after_get_read_connection(): void
    {
        $manager = new ConnectionManager($this->makeDriver());
        $manager->getReadConnection();

        $this->assertTrue($manager->isConnected());
    }

    public function test_get_write_connection_returns_same_instance_on_subsequent_calls(): void
    {
        $manager = new ConnectionManager($this->makeDriver());

        $this->assertSame($manager->getWriteConnection(), $manager->getWriteConnection());
    }

    public function test_get_read_connection_returns_write_connection_when_no_replicas(): void
    {
        $manager = new ConnectionManager($this->makeDriver());

        $this->assertSame($manager->getWriteConnection(), $manager->getReadConnection());
    }

    public function test_get_read_connection_returns_replica_when_available(): void
    {
        $manager = new ConnectionManager($this->makeDriver(readDsns: ['sqlite::memory:']));

        $this->assertNotSame($manager->getWriteConnection(), $manager->getReadConnection());
    }

    public function test_get_read_connection_with_single_replica_always_returns_same_instance(): void
    {
        $manager = new ConnectionManager($this->makeDriver(readDsns: ['sqlite::memory:']));

        $first = $manager->getReadConnection();
        $second = $manager->getReadConnection();
        $third = $manager->getReadConnection();

        $this->assertSame($first, $second);
        $this->assertSame($first, $third);
    }

    public function test_get_read_connection_round_robins_multiple_replicas(): void
    {
        $manager = new ConnectionManager($this->makeDriver(readDsns: [
            'sqlite::memory:',
            'sqlite::memory:',
        ]));

        $first = $manager->getReadConnection();
        $second = $manager->getReadConnection();

        $this->assertNotSame($first, $second);
        $this->assertSame($first, $manager->getReadConnection());
        $this->assertSame($second, $manager->getReadConnection());
    }

    public function test_sticky_write_redirects_reads_to_write_connection(): void
    {
        $manager = new ConnectionManager($this->makeDriver(
            readDsns: ['sqlite::memory:'],
            sticky: true
        ));

        $write = $manager->getWriteConnection();

        $this->assertSame($write, $manager->getReadConnection());
    }

    public function test_non_sticky_write_does_not_redirect_reads(): void
    {
        $manager = new ConnectionManager($this->makeDriver(
            readDsns: ['sqlite::memory:'],
            sticky: false
        ));

        $write = $manager->getWriteConnection();

        $this->assertNotSame($write, $manager->getReadConnection());
    }

    public function test_reset_sticky_write_allows_reads_to_use_replicas(): void
    {
        $manager = new ConnectionManager($this->makeDriver(
            readDsns: ['sqlite::memory:'],
            sticky: true
        ));

        $write = $manager->getWriteConnection();
        $this->assertSame($write, $manager->getReadConnection());

        $manager->resetStickyWrite();

        $this->assertNotSame($write, $manager->getReadConnection());
    }

    public function test_disconnect_marks_as_not_connected(): void
    {
        $manager = new ConnectionManager($this->makeDriver());
        $manager->getWriteConnection();
        $manager->disconnect();

        $this->assertFalse($manager->isConnected());
    }

    public function test_disconnect_when_not_connected_is_a_no_op(): void
    {
        $manager = new ConnectionManager($this->makeDriver());

        // should not throw
        $manager->disconnect();

        $this->assertFalse($manager->isConnected());
    }

    public function test_reconnects_after_disconnect(): void
    {
        $manager = new ConnectionManager($this->makeDriver());

        $first = $manager->getWriteConnection();
        $manager->disconnect();
        $second = $manager->getWriteConnection();

        $this->assertNotSame($first, $second);
    }

    public function test_disconnect_resets_sticky_write(): void
    {
        $manager = new ConnectionManager($this->makeDriver(
            readDsns: ['sqlite::memory:'],
            sticky: true
        ));

        $manager->getWriteConnection();
        $manager->disconnect();

        // get read before write to verify writeConnectionUsed was reset
        $read = $manager->getReadConnection();
        $write = $manager->getWriteConnection();

        $this->assertNotSame($write, $read);
    }

    public function test_disconnect_resets_round_robin_index(): void
    {
        $manager = new ConnectionManager($this->makeDriver(readDsns: [
            'sqlite::memory:',
            'sqlite::memory:',
        ]));

        $first = $manager->getReadConnection();
        $manager->getReadConnection();
        $manager->disconnect();

        // after reconnect, round robin should start from the first replica again
        $this->assertNotSame($first, $manager->getReadConnection());
    }

    public function test_get_driver_name(): void
    {
        $manager = new ConnectionManager($this->makeDriver(name: 'pgsql'));

        $this->assertSame('pgsql', $manager->getDriverName());
    }
}
