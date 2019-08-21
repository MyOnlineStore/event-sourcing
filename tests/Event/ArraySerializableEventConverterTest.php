<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Event;

use MyOnlineStore\EventSourcing\Event\ArraySerializable;
use MyOnlineStore\EventSourcing\Event\ArraySerializableEventConverter;
use MyOnlineStore\EventSourcing\Event\BaseEvent;
use MyOnlineStore\EventSourcing\Service\Encoder;
use PHPUnit\Framework\TestCase;

final class ArraySerializableEventConverterTest extends TestCase
{
    /** @var ArraySerializableEventConverter */
    private $converter;

    /** @var Encoder */
    private $encoder;

    protected function setUp(): void
    {
        $this->converter = new ArraySerializableEventConverter(
            $this->encoder = $this->createMock(Encoder::class)
        );
    }

    public function testCreateFromArray(): void
    {
        $data = [
            'event_name' => BaseEvent::class,
            'event_id' => '8311db73-de57-4fb0-b8bc-84dc37296c1e',
            'created_at' => '2019-08-21T14:31:30.374+02:00',
            'payload' => 'foobar',
            'metadata' => 'bazqux',
            'version' => 5,
        ];

        $this->encoder->expects(self::exactly(2))
            ->method('decode')
            ->withConsecutive(['foobar'], ['bazqux'])
            ->willReturnOnConsecutiveCalls(
                ['foo' => 'bar'],
                ['baz' => 'qux']
            );

        self::assertInstanceOf(BaseEvent::class, $this->converter->createFromArray($data));
    }

    public function testConvertToArray(): void
    {
        $event = $this->createMock(ArraySerializable::class);
        $event->expects(self::once())
            ->method('toArray')
            ->willReturn(
                [
                    'other' => 123,
                    'payload' => ['foo' => 'bar'],
                    'metadata' => ['baz' => 'qux'],
                ]
            );

        $this->encoder->expects(self::exactly(2))
            ->method('encode')
            ->withConsecutive(
                [['foo' => 'bar']],
                [['baz' => 'qux']]
            )
            ->willReturnOnConsecutiveCalls('foobar', 'bazqux');

        self::assertSame(
            [
                'other' => 123,
                'payload' => 'foobar',
                'metadata' => 'bazqux',
            ],
            $this->converter->convertToArray($event)
        );
    }
}
