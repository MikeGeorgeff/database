<?php

namespace Georgeff\Database\Connection;

final class MySqlDriver extends AbstractDriver
{
    /**
     * @param string[] $readHosts
     */
    public function __construct(
        private readonly string $host,
        private readonly string $database,
        private readonly string $username,
        private readonly string $password,
        private readonly string $port = '3306',
        private readonly string $charset = 'utf8mb4',
        private readonly array $readHosts = [],
        private readonly bool $sticky = false
    ) {}

    /**
     * Create from an array
     *
     * @param array{
     *     hosts: array{write: string, read?: string[]},
     *     database: string,
     *     username: string,
     *     password: string,
     *     port?: string,
     *     charset?: string,
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
            port: $config['port'] ?? '3306',
            charset: $config['charset'] ?? 'utf8mb4',
            readHosts: $config['hosts']['read'] ?? [],
            sticky: $config['sticky'] ?? false
        );
    }

    public function getName(): string
    {
        return 'mysql';
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
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $host,
            $this->port,
            $this->database,
            $this->charset
        );
    }
}
