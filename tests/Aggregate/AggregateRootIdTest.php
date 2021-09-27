<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Aggregate;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Exception\AssertionFailed;
use PHPUnit\Framework\TestCase;

final class AggregateRootIdTest extends TestCase
{
    public function testEquals(): void
    {
        $aggregateRootId = AggregateRootId::fromString('8311db73-de57-4fb0-b8bc-84dc37296c1e');

        self::assertTrue($aggregateRootId->equals($aggregateRootId));
        self::assertTrue($aggregateRootId->equals(AggregateRootId::fromString('8311db73-de57-4fb0-b8bc-84dc37296c1e')));
        self::assertFalse(
            $aggregateRootId->equals(AggregateRootId::fromString('8311db73-de57-4fb0-b8bc-84dc37296c1f'))
        );
    }

    public function testFromAggregateRootId(): void
    {
        $aggregateRootId = AggregateRootId::generate();

        $concreteAggregateRootId = ConcreteAggregateRootId::fromAggregateRootId($aggregateRootId);

        self::assertInstanceOf(ConcreteAggregateRootId::class, $concreteAggregateRootId);
        self::assertSame($aggregateRootId->toString(), $concreteAggregateRootId->toString());
    }

    public function testFromStringWithValidUuid(): void
    {
        $aggregateRootId = AggregateRootId::fromString('8311db73-de57-4fb0-b8bc-84dc37296c1e');

        self::assertSame('8311db73-de57-4fb0-b8bc-84dc37296c1e', (string) $aggregateRootId);
        self::assertSame('8311db73-de57-4fb0-b8bc-84dc37296c1e', $aggregateRootId->toString());
    }

    public function testFromStringWithInvalidUuid(): void
    {
        $this->expectException(AssertionFailed::class);
        AggregateRootId::fromString('invalid-uuid');
    }

    public function testGenerate(): void
    {
        self::assertInstanceOf(AggregateRootId::class, AggregateRootId::generate());
    }
}
