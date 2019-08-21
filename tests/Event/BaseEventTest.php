<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Event;

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
                'created_at' => '2019-08-21T14:31:30.374+02:00',
                'metadata' => [],
                'payload' => [],
                'version' => 5,
            ],
        ];
        yield [
            [
                'event_id' => '8311db73-de57-4fb0-b8bc-84dc37296c1e',
                'metadata' => [],
                'payload' => [],
                'version' => 5,
            ],
        ];
        yield [
            [
                'event_id' => '8311db73-de57-4fb0-b8bc-84dc37296c1e',
                'created_at' => '2019-08-21T14:31:30.374+02:00',
                'payload' => [],
                'version' => 5,
            ],
        ];
        yield [
            [
                'event_id' => '8311db73-de57-4fb0-b8bc-84dc37296c1e',
                'created_at' => '2019-08-21T14:31:30.374+02:00',
                'metadata' => [],
                'version' => 5,
            ],
        ];
        yield [
            [
                'event_id' => '8311db73-de57-4fb0-b8bc-84dc37296c1e',
                'created_at' => '2019-08-21T14:31:30.374+02:00',
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
                'event_id' => '8311db73-de57-4fb0-b8bc-84dc37296c1e',
                'created_at' => '2019-08-21T14:31:30.374+02:00',
                'payload' => ['foo' => 'bar'],
                'metadata' => ['baz' => 'qux'],
                'version' => 5,
            ]
        );

        self::assertEquals(EventId::fromString('8311db73-de57-4fb0-b8bc-84dc37296c1e'), $event->getId());
        self::assertEquals(
            \DateTimeImmutable::createFromFormat(\DATE_RFC3339_EXTENDED, '2019-08-21T14:31:30.374+02:00'),
            $event->getCreatedAt()
        );
        self::assertSame(['foo' => 'bar'], $event->getPayload());
        self::assertSame(['baz' => 'qux'], $event->getMetadata());
        self::assertSame(5, $event->getVersion());
    }

    public function testOccurWithMetadata(): void
    {
        $event = BaseEvent::occur(['foo' => 'bar'], ['baz' => 'qux']);

        self::assertSame(1, $event->getVersion());
        self::assertSame(['foo' => 'bar'], $event->getPayload());
        self::assertSame(['baz' => 'qux'], $event->getMetadata());
        self::assertInstanceOf(EventId::class, $event->getId());
        self::assertInstanceOf(\DateTimeImmutable::class, $event->getCreatedAt());
    }

    public function testOccurWithoutMetadata(): void
    {
        $event = BaseEvent::occur(['foo' => 'bar']);

        self::assertSame(1, $event->getVersion());
        self::assertSame(['foo' => 'bar'], $event->getPayload());
        self::assertSame([], $event->getMetadata());
        self::assertInstanceOf(EventId::class, $event->getId());
        self::assertInstanceOf(\DateTimeImmutable::class, $event->getCreatedAt());
    }

    public function testToArray(): void
    {
        $event = BaseEvent::occur(['foo' => 'bar']);
        $array = $event->toArray();

        self::assertSame($array['event_id'], (string) $event->getId());
        self::assertSame($array['created_at'], $event->getCreatedAt()->format(\DATE_RFC3339_EXTENDED));
        self::assertSame($array['metadata'], $event->getMetadata());
        self::assertSame($array['payload'], $event->getPayload());
        self::assertSame($array['version'], $event->getVersion());
    }

    public function testWithMetadata(): void
    {
        $event = BaseEvent::occur(['foo' => 'bar']);
        $withMetadata = $event->withMetadata('baz', 'qux');

        self::assertSame([], $event->getMetadata());
        self::assertSame(['baz' => 'qux'], $withMetadata->getMetadata());
    }

    public function testWithVersion(): void
    {
        $event = BaseEvent::occur(['foo' => 'bar']);
        $withVersion = $event->withVersion(5);

        self::assertSame(1, $event->getVersion());
        self::assertSame(5, $withVersion->getVersion());
    }
}
