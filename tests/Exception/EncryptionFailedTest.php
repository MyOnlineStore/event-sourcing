<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Exception;

use MyOnlineStore\EventSourcing\Exception\EncryptionFailed;
use MyOnlineStore\EventSourcing\Exception\EventSourcingException;
use PHPUnit\Framework\TestCase;

final class EncryptionFailedTest extends TestCase
{
    public function testToDecrypt(): void
    {
        self::assertInstanceOf(EventSourcingException::class, EncryptionFailed::toDecrypt());
    }

    public function testToDecryptWithPrevious(): void
    {
        $exception = EncryptionFailed::toDecrypt($previous = new \Exception());

        self::assertInstanceOf(EventSourcingException::class, $exception);
        self::assertSame($previous, $exception->getPrevious());
    }

    public function testToEncrypt(): void
    {
        self::assertInstanceOf(EventSourcingException::class, EncryptionFailed::toEncrypt());
    }

    public function testToEncryptWithPrevious(): void
    {
        $exception = EncryptionFailed::toEncrypt($previous = new \Exception());

        self::assertInstanceOf(EventSourcingException::class, $exception);
        self::assertSame($previous, $exception->getPrevious());
    }
}
