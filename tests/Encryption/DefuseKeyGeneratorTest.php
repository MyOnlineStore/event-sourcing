<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Encryption;

use Defuse\Crypto\Key;
use MyOnlineStore\EventSourcing\Encryption\DefuseKeyGenerator;
use PHPUnit\Framework\TestCase;

final class DefuseKeyGeneratorTest extends TestCase
{
    private DefuseKeyGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new DefuseKeyGenerator();
    }

    public function testGenerate(): void
    {
        $key = $this->generator->generate();

        self::assertNotEmpty($key);

        $defuseKey = Key::loadFromAsciiSafeString($key);

        self::assertSame($key, $defuseKey->saveToAsciiSafeString());
    }
}
