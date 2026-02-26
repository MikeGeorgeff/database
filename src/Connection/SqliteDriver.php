<?php

namespace Georgeff\Database\Connection;

final class SqliteDriver extends AbstractDriver
{
    public function __construct(private readonly string $database = ':memory:') {}

    /**
     * @param array{database?: string} $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            database: $config['database'] ?? ':memory:'
        );
    }

    public function getName(): string
    {
        return 'sqlite';
    }

    public function getDsn(): string
    {
        return 'sqlite:' . $this->database;
    }

    public function getUsername(): ?string
    {
        return null;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function hasReadReplicas(): bool
    {
        return false;
    }

    public function isSticky(): bool
    {
        return false;
    }

    public function getDsnForReadReplicas(): array
    {
        return [];
    }
}
