<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Event;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\BaseEvent;
use MyOnlineStore\EventSourcing\Event\EventId;
use MyOnlineStore\EventSourcing\Exception\AssertionFailed;
use PHPUnit\Framework\TestCase;

final class BaseEventTest extends TestCase
{
    public function fromArrayInvalidDatasetProvider(): \Generator
    {
        yield [[]];
        yield [
            [
                'aggregateId' => '7311db73-de57-4fb0-b8bc-84dc37296c1f',
                'createdAt' => '2019-08-21 14:31:30.374870',
                'metadata' => [],
                'payload' => [],
                'version' => 5,
            ],
        ];
        yield [
            [
                'eventId' => '8311db73-de57-4fb0-b8bc-84dc37296c1e',
                'createdAt' => '2019-08-21 14:31:30.374870',
                'metadata' => [],
                'payload' => [],
                'version' => 5,
            ],
        ];
        yield [
            [
                'eventId' => '8311db73-de57-4fb0-b8bc-84dc37296c1e',
                'aggregateId' => '7311db73-de57-4fb0-b8bc-84dc37296c1f',
                'metadata' => [],
                'payload' => [],
                'version' => 5,
            ],
        ];
        yield [
            [
                'eventId' => '8311db73-de57-4fb0-b8bc-84dc37296c1e',
                'aggregateId' => '7311db73-de57-4fb0-b8bc-84dc37296c1f',
                'createdAt' => '2019-08-21 14:31:30.374870',
                'payload' => [],
                'version' => 5,
            ],
        ];
        yield [
            [
                'eventId' => '8311db73-de57-4fb0-b8bc-84dc37296c1e',
                'aggregateId' => '7311db73-de57-4fb0-b8bc-84dc37296c1f',
                'createdAt' => '2019-08-21 14:31:30.374870',
                'metadata' => [],
                'version' => 5,
            ],
        ];
        yield [
            [
                'eventId' => '8311db73-de57-4fb0-b8bc-84dc37296c1e',
                'aggregateId' => '7311db73-de57-4fb0-b8bc-84dc37296c1f',
                'createdAt' => '2019-08-21 14:31:30.374870',
                'metadata' => [],
                'payload' => [],
            ],
        ];
    }

    /**
     * @dataProvider fromArrayInvalidDatasetProvider
     *
     * @param mixed[] $invalidDataset
     */
    public function testFromArrayWithInvalidDataset(array $invalidDataset): void
    {
        $this->expectException(AssertionFailed::class);
        BaseEvent::fromArray($invalidDataset);
    }

    public function testFromArray(): void
    {
        $event = BaseEvent::fromArray(
            [
                'eventId' => '8311db73-de57-4fb0-b8bc-84dc37296c1e',
                'aggregateId' => '7311db73-de57-4fb0-b8bc-84dc37296c1f',
                'createdAt' => '2019-08-21 14:31:30.374870',
                'payload' => ['foo' => 'bar'],
                'metadata' => ['baz' => 'qux'],
                'version' => 5,
            ]
        );

        self::assertEquals(EventId::fromString('8311db73-de57-4fb0-b8bc-84dc37296c1e'), $event->getId());
        self::assertEquals(
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', '2019-08-21 14:31:30.374870'),
            $event->getCreatedAt()
        );
        self::assertSame(['foo' => 'bar'], $event->getPayload());
        self::assertSame(['baz' => 'qux'], $event->getMetadata());
        self::assertSame(5, $event->getVersion());
    }

    public function testOccurWithMetadata(): void
    {
        $aggregateId = AggregateRootId::fromString('7311db73-de57-4fb0-b8bc-84dc37296c1f');
        $event = BaseEvent::occur($aggregateId, ['foo' => 'bar'], ['baz' => 'qux']);

        self::assertEquals($aggregateId, $event->getAggregateId());
        self::assertSame(1, $event->getVersion());
        self::assertSame(['foo' => 'bar'], $event->getPayload());
        self::assertSame(['baz' => 'qux'], $event->getMetadata());
        self::assertInstanceOf(EventId::class, $event->getId());
        self::assertInstanceOf(\DateTimeImmutable::class, $event->getCreatedAt());
    }

    public function testOccurWithoutMetadata(): void
    {
        $aggregateId = AggregateRootId::fromString('7311db73-de57-4fb0-b8bc-84dc37296c1f');
        $event = BaseEvent::occur($aggregateId, ['foo' => 'bar']);

        self::assertEquals($aggregateId, $event->getAggregateId());
        self::assertSame(1, $event->getVersion());
        self::assertSame(['foo' => 'bar'], $event->getPayload());
        self::assertSame([], $event->getMetadata());
        self::assertInstanceOf(EventId::class, $event->getId());
        self::assertInstanceOf(\DateTimeImmutable::class, $event->getCreatedAt());
    }

    public function testToArray(): void
    {
        $aggregateId = AggregateRootId::fromString('7311db73-de57-4fb0-b8bc-84dc37296c1f');
        $event = BaseEvent::occur($aggregateId, ['foo' => 'bar']);
        $array = $event->toArray();

        self::assertSame($array['eventId'], (string) $event->getId());
        self::assertSame($array['aggregateId'], '7311db73-de57-4fb0-b8bc-84dc37296c1f');
        self::assertSame($array['createdAt'], $event->getCreatedAt()->format('Y-m-d H:i:s.u'));
        self::assertSame($array['metadata'], $event->getMetadata());
        self::assertSame($array['payload'], $event->getPayload());
        self::assertSame($array['version'], $event->getVersion());
    }

    public function testWithMetadata(): void
    {
        $aggregateId = AggregateRootId::fromString('7311db73-de57-4fb0-b8bc-84dc37296c1f');
        $event = BaseEvent::occur($aggregateId, ['foo' => 'bar']);
        $withMetadata = $event->withMetadata('baz', 'qux');

        self::assertSame([], $event->getMetadata());
        self::assertSame(['baz' => 'qux'], $withMetadata->getMetadata());
    }

    public function testWithVersion(): void
    {
        $aggregateId = AggregateRootId::fromString('7311db73-de57-4fb0-b8bc-84dc37296c1f');
        $event = BaseEvent::occur($aggregateId, ['foo' => 'bar']);
        $withVersion = $event->withVersion(5);

        self::assertSame(1, $event->getVersion());
        self::assertSame(5, $withVersion->getVersion());
    }
}
