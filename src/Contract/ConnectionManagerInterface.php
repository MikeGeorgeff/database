<?php

namespace Georgeff\Database\Contract;

use Aura\Sql\ExtendedPdoInterface;

interface ConnectionManagerInterface
{
    public function getWriteConnection(): ExtendedPdoInterface;

    public function getReadConnection(): ExtendedPdoInterface;

    public function disconnect(): void;

    public function resetStickyWrite(): void;

    public function getDriverName(): string;

    public function isConnected(): bool;
}
