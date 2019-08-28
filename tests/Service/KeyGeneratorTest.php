<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Service;

use MyOnlineStore\EventSourcing\Service\KeyGenerator;
use PHPUnit\Framework\TestCase;

final class KeyGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        $generator = new KeyGenerator(32);

        // 32 bytes translates to 64 hex chars
        self::assertRegExp('#^[0-9a-f]{64}$#', $generator->generate());
    }
}
