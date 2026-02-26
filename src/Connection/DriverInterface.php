<?php

namespace Georgeff\Database\Connection;

interface DriverInterface
{
    public function getName(): string;

    /**
     * Get the DSN for the read/default connection
     */
    public function getDsn(): string;

    public function getUsername(): ?string;

    public function getPassword(): ?string;

    public function hasReadReplicas(): bool;

    /**
     * Indicates if the write connection should be used
     * for subsequent reads during the current cycle
     */
    public function isSticky(): bool;

    /**
     * @return string[]
     */
    public function getDsnForReadReplicas(): array;

    /**
     * @return array<int, mixed>
     */
    public function getPdoOptions(): array;
}
