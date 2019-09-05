<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Encryption;

use MyOnlineStore\EventSourcing\Encryption\RandomBytesKeyGenerator;
use PHPUnit\Framework\TestCase;

final class RandomBytesKeyGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        $generator = new RandomBytesKeyGenerator(32);

        // 32 bytes translates to 64 hex chars
        self::assertRegExp('#^[0-9a-f]{64}$#', $generator->generate());
    }
}
