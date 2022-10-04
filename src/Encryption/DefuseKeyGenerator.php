<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Encryption;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;

final class DefuseKeyGenerator implements KeyGenerator
{
    /** @throws EnvironmentIsBrokenException */
    public function generate(): string
    {
        return Key::createNewRandomKey()->saveToAsciiSafeString();
    }
}
