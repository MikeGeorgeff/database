<?php

namespace Georgeff\Database\Connection;

use PDO;

abstract class AbstractDriver implements DriverInterface
{
    /**
     * @var array<int, mixed>
     */
    private array $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    public function getPdoOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<int, mixed> $options
     */
    public function setPdoOptions(array $options): void
    {
        $this->options = $options;
    }
}
