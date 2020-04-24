<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Exception;

use MyOnlineStore\EventSourcing\Exception\EncodingFailed;
use PHPUnit\Framework\TestCase;

final class EncodingFailedTest extends TestCase
{
    public function testFromPrevious(): void
    {
        $previous = new \RuntimeException('encoding failed', 500);
        $exception = EncodingFailed::fromPrevious($previous);

        self::assertSame('encoding failed', $exception->getMessage());
        self::assertSame(500, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }
}
