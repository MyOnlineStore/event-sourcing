<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Exception;

use MyOnlineStore\EventSourcing\Exception\EncodingFailed;
use MyOnlineStore\EventSourcing\Exception\EventSourcingException;
use PHPUnit\Framework\TestCase;

final class EncodingFailedTest extends TestCase
{
    public function testToDecode(): void
    {
        $exception = EncodingFailed::toDecode('decoding failed', 500);

        self::assertInstanceOf(EventSourcingException::class, $exception);
        self::assertSame('decoding failed', $exception->getMessage());
        self::assertSame(500, $exception->getCode());
    }

    public function testToEncode(): void
    {
        $exception = EncodingFailed::toEncode('encoding failed', 500);

        self::assertInstanceOf(EventSourcingException::class, $exception);
        self::assertSame('encoding failed', $exception->getMessage());
        self::assertSame(500, $exception->getCode());
    }
}
