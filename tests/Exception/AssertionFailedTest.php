<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Exception;

use MyOnlineStore\EventSourcing\Exception\AssertionFailed;
use MyOnlineStore\EventSourcing\Exception\EventSourcingException;
use PHPUnit\Framework\TestCase;

final class AssertionFailedTest extends TestCase
{
    public function testConstructor(): void
    {
        $exception = new AssertionFailed(
            'AssertionFailed',
            400,
            'foo',
            'bar',
            ['foo' => 'bar']
        );

        self::assertInstanceOf(EventSourcingException::class, $exception);
        self::assertSame('AssertionFailed', $exception->getMessage());
        self::assertSame(400, $exception->getCode());
        self::assertSame('foo', $exception->getPropertyPath());
        self::assertSame('bar', $exception->getValue());
        self::assertSame(['foo' => 'bar'], $exception->getConstraints());
    }
}
