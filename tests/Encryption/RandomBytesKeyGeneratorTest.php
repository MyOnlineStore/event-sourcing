<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Encryption;

use MyOnlineStore\EventSourcing\Encryption\RandomBytesKeyGenerator;
use PHPUnit\Framework\TestCase;

final class RandomBytesKeyGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        $generator = new RandomBytesKeyGenerator(64);

        self::assertRegExp('#^[0-9a-zA-Z]{64}$#', $generator->generate());
    }
}
