<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Encryption;

use Defuse\Crypto\Key;
use MyOnlineStore\EventSourcing\Encryption\DefuseCryptoEncrypter;
use MyOnlineStore\EventSourcing\Exception\EncryptionFailed;
use PHPUnit\Framework\TestCase;

final class DefuseCryptoEncrypterTest extends TestCase
{
    private DefuseCryptoEncrypter $encrypter;

    protected function setUp(): void
    {
        $this->encrypter = new DefuseCryptoEncrypter();
    }

    public function testEncryption(): void
    {
        $key = Key::createNewRandomKey()->saveToAsciiSafeString();
        $value = 'Super secret value nobody must know';

        $encrypted = $this->encrypter->encrypt($key, $value);

        self::assertNotEmpty($encrypted);
        self::assertSame($value, $this->encrypter->decrypt($key, $encrypted));
    }

    public function testFailedToDecryptException(): void
    {
        $this->expectException(EncryptionFailed::class);

        $this->encrypter->decrypt('', '');
    }

    public function testFailedToEncryptException(): void
    {
        $this->expectException(EncryptionFailed::class);

        $this->encrypter->encrypt('', '');
    }
}
