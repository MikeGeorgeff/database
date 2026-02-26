<?php

namespace Georgeff\Database\Connection;

use Aura\Sql\ExtendedPdo;
use Aura\Sql\ExtendedPdoInterface;
use Georgeff\Database\Contract\ConnectionManagerInterface;

final class ConnectionManager implements ConnectionManagerInterface
{
    /**
     * Default/read connection
     */
    private ?ExtendedPdoInterface $connection = null;

    /**
     * @var ExtendedPdoInterface[]
     */
    private array $readConnections = [];

    private bool $writeConnectionUsed = false;

    private int $readConnectionIndex = 0;

    public function __construct(private readonly DriverInterface $driver) {}

    /**
     * @phpstan-assert !null $this->connection
     */
    private function connect(): void
    {
        $this->connection = new ExtendedPdo(
            $this->driver->getDsn(),
            $this->driver->getUsername(),
            $this->driver->getPassword(),
            $this->driver->getPdoOptions()
        );

        if ($this->driver->hasReadReplicas()) {
            foreach ($this->driver->getDsnForReadReplicas() as $index => $dsn) {
                $this->readConnections[$index] = new ExtendedPdo(
                    $dsn,
                    $this->driver->getUsername(),
                    $this->driver->getPassword(),
                    $this->driver->getPdoOptions()
                );
            }
        }
    }

    public function getWriteConnection(): ExtendedPdoInterface
    {
        if (!$this->connection) {
            $this->connect();
        }

        if ($this->driver->isSticky()) {
            $this->writeConnectionUsed = true;
        }

        return $this->connection;
    }

    public function getReadConnection(): ExtendedPdoInterface
    {
        if (!$this->connection) {
            $this->connect();
        }

        // if there was a previous write or no read connections return the write connection
        if ($this->writeConnectionUsed || [] === $this->readConnections) {
            return $this->connection;
        }

        $replicas = count($this->readConnections);

        if (1 === $replicas) {
            return $this->readConnections[0];
        }

        $connection = $this->readConnections[$this->readConnectionIndex];

        $this->readConnectionIndex = ($this->readConnectionIndex + 1) % $replicas;

        return $connection;
    }

    public function disconnect(): void
    {
        if (!$this->connection) {
            return;
        }

        $this->connection->disconnect();

        foreach ($this->readConnections as $replica) {
            $replica->disconnect();
        }

        $this->connection = null;
        $this->readConnections = [];
        $this->readConnectionIndex = 0;
        $this->writeConnectionUsed = false;
    }

    public function resetStickyWrite(): void
    {
        $this->writeConnectionUsed = false;
    }

    public function isConnected(): bool
    {
        return isset($this->connection);
    }

    public function getDriverName(): string
    {
        return $this->driver->getName();
    }
}
