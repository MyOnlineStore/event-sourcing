<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Exception;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Exception\EventSourcingException;
use MyOnlineStore\EventSourcing\Exception\ReadModelNotFound;
use PHPUnit\Framework\TestCase;

class ReadModelNotFoundTest extends TestCase
{
    public function testWithAggregateRootId(): void
    {
        $id = AggregateRootId::generate();
        $exception = ReadModelNotFound::withAggregateRootId($id, 'foobar');

        self::assertInstanceOf(EventSourcingException::class, $exception);
        self::assertStringContainsString('foobar', $exception->getMessage());
        self::assertStringContainsString((string) $id, $exception->getMessage());
    }
}
