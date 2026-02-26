<?php

namespace Georgeff\Database\Connection;

use InvalidArgumentException;

final class PgsqlDriver extends AbstractDriver
{
    /**
     * @param string[] $readHosts
     */
    public function __construct(
        private readonly string $host,
        private readonly string $database,
        private readonly string $username,
        private readonly string $password,
        private readonly string $port = '5432',
        private readonly string $schema = 'public',
        private readonly string $sslmode = 'prefer',
        private readonly array $readHosts = [],
        private readonly bool $sticky = false
    ) {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_$]*$/', $this->schema)) {
            throw new InvalidArgumentException(
                "Invalid schema name [{$this->schema}]"
            );
        }
    }

    /**
     * Create from an array
     *
     * @param array{
     *     hosts: array{write: string, read?: string[]},
     *     database: string,
     *     username: string,
     *     password: string,
     *     port?: string,
     *     schema?: string,
     *     sslmode?: string,
     *     sticky?: bool
     * } $config
     */
    public static function fromArray(array $config): self
    {
        return new self(
            host: $config['hosts']['write'],
            database: $config['database'],
            username: $config['username'],
            password: $config['password'],
            port: $config['port'] ?? '5432',
            schema: $config['schema'] ?? 'public',
            sslmode: $config['sslmode'] ?? 'prefer',
            readHosts: $config['hosts']['read'] ?? [],
            sticky: $config['sticky'] ?? false
        );
    }

    public function getName(): string
    {
        return 'pgsql';
    }

    public function getDsn(): string
    {
        return $this->buildDsn($this->host);
    }

    public function getDsnForReadReplicas(): array
    {
        if (!$this->hasReadReplicas()) {
            return [];
        }

        return array_values(array_map(
            fn(string $host) => $this->buildDsn($host),
            $this->readHosts
        ));
    }

    public function getUsername(): ?string // @phpstan-ignore return.unusedType
    {
        return $this->username;
    }

    public function getPassword(): ?string // @phpstan-ignore return.unusedType
    {
        return $this->password;
    }

    public function hasReadReplicas(): bool
    {
        return [] !== $this->readHosts;
    }

    public function isSticky(): bool
    {
        return $this->sticky;
    }

    private function buildDsn(string $host): string
    {
        return sprintf(
            "pgsql:host=%s;port=%s;dbname=%s;sslmode=%s;options='--search_path=%s'",
            $host,
            $this->port,
            $this->database,
            $this->sslmode,
            $this->schema
        );
    }
}
