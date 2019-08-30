<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Encryption;

use MyOnlineStore\EventSourcing\Encryption\ZendBlockCipherEncrypter;
use MyOnlineStore\EventSourcing\Exception\EncryptionFailed;
use PHPUnit\Framework\TestCase;
use Zend\Crypt\BlockCipher;
use Zend\Crypt\Exception\InvalidArgumentException;

final class ZendBlockCipherEncrypterTest extends TestCase
{
    /** @var BlockCipher */
    private $blockCipher;

    /** @var ZendBlockCipherEncrypter */
    private $encrypter;

    protected function setUp(): void
    {
        $this->encrypter = new ZendBlockCipherEncrypter(
            $this->blockCipher = $this->createMock(BlockCipher::class)
        );
    }

    public function testEncrypt(): void
    {
        $this->blockCipher->expects(self::once())
            ->method('setKey')
            ->with('foo');

        $this->blockCipher->expects(self::once())
            ->method('encrypt')
            ->with('bar')
            ->willReturn('bar_encrypted');

        self::assertSame('bar_encrypted', $this->encrypter->encrypt('foo', 'bar'));
    }

    public function testEncryptWithInvalidKey(): void
    {
        $this->blockCipher->expects(self::once())
            ->method('setKey')
            ->with('')
            ->willThrowException(new InvalidArgumentException());

        $this->expectException(EncryptionFailed::class);

        $this->encrypter->encrypt('', 'bar');
    }

    public function testEncryptFailed(): void
    {
        $this->blockCipher->expects(self::once())
            ->method('setKey')
            ->with('foo');

        $this->blockCipher->expects(self::once())
            ->method('encrypt')
            ->with('bar')
            ->willThrowException(new InvalidArgumentException());

        $this->expectException(EncryptionFailed::class);

        $this->encrypter->encrypt('foo', 'bar');
    }

    public function testDecrypt(): void
    {
        $this->blockCipher->expects(self::once())
            ->method('setKey')
            ->with('foo');

        $this->blockCipher->expects(self::once())
            ->method('decrypt')
            ->with('bar_encrypted')
            ->willReturn('bar');

        self::assertSame('bar', $this->encrypter->decrypt('foo', 'bar_encrypted'));
    }

    public function testDecryptWithInvalidKey(): void
    {
        $this->blockCipher->expects(self::once())
            ->method('setKey')
            ->with('')
            ->willThrowException(new InvalidArgumentException());

        $this->expectException(EncryptionFailed::class);

        $this->encrypter->decrypt('', 'bar_encrypted');
    }

    public function testDecryptFailed(): void
    {
        $this->blockCipher->expects(self::once())
            ->method('setKey')
            ->with('foo');

        $this->blockCipher->expects(self::once())
            ->method('decrypt')
            ->with('bar_encrypted')
            ->willThrowException(new InvalidArgumentException());

        $this->expectException(EncryptionFailed::class);

        $this->encrypter->decrypt('foo', 'bar_encrypted');
    }

    public function testDecryptFailedAndReturnsFalse(): void
    {
        $this->blockCipher->expects(self::once())
            ->method('setKey')
            ->with('foo');

        $this->blockCipher->expects(self::once())
            ->method('decrypt')
            ->with('bar_encrypted')
            ->willReturn(false);

        $this->expectException(EncryptionFailed::class);

        $this->encrypter->decrypt('foo', 'bar_encrypted');
    }
}
