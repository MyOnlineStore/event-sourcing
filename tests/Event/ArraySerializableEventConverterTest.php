<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Event;

use MyOnlineStore\EventSourcing\Event\ArraySerializable;
use MyOnlineStore\EventSourcing\Event\ArraySerializableEventConverter;
use MyOnlineStore\EventSourcing\Event\BaseEvent;
use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Exception\AssertionFailed;
use PHPUnit\Framework\TestCase;

final class ArraySerializableEventConverterTest extends TestCase
{
    /** @var ArraySerializableEventConverter */
    private $converter;

    /** @var StreamMetadata */
    private $streamMetadata;

    protected function setUp(): void
    {
        $this->converter = new ArraySerializableEventConverter();
        $this->streamMetadata = new StreamMetadata([]);
    }

    public function testCreateFromArray(): void
    {
        $data = [
            'event_id' => '8311db73-de57-4fb0-b8bc-84dc37296c1e',
            'aggregate_id' => '7311db73-de57-4fb0-b8bc-84dc37296c1f',
            'created_at' => '2019-08-21T14:31:30.374+02:00',
            'payload' => ['foo' => 'bar'],
            'metadata' => ['baz' => 'qux'],
            'version' => 5,
        ];

        self::assertInstanceOf(
            BaseEvent::class,
            $this->converter->createFromArray(BaseEvent::class, $data, $this->streamMetadata)
        );
    }

    public function testCreateFromArrayWithNonClassEventName(): void
    {
        $this->expectException(AssertionFailed::class);
        $this->converter->createFromArray('foobar', [], $this->streamMetadata);
    }

    public function testCreateFromArrayWithInvalidEventName(): void
    {
        $this->expectException(AssertionFailed::class);
        $this->converter->createFromArray(\stdClass::class, [], $this->streamMetadata);
    }

    public function testConvertToArray(): void
    {
        $event = $this->createMock(ArraySerializable::class);
        $event->expects(self::once())
            ->method('toArray')
            ->willReturn(
                $data = [
                    'other' => 123,
                    'payload' => ['foo' => 'bar'],
                    'metadata' => ['baz' => 'qux'],
                ]
            );

        self::assertSame($data, $this->converter->convertToArray($event, $this->streamMetadata));
    }

    public function testConvertToArrayWithInvalidEventImplementation(): void
    {
        $this->expectException(AssertionFailed::class);
        $this->converter->convertToArray($this->createMock(Event::class), $this->streamMetadata);
    }
}
