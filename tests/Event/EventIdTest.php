<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Event;

use MyOnlineStore\EventSourcing\Event\EventId;
use MyOnlineStore\EventSourcing\Exception\AssertionFailed;
use PHPUnit\Framework\TestCase;

final class EventIdTest extends TestCase
{
    public function testFromStringWithValidUuid(): void
    {
        $eventId = EventId::fromString('8311db73-de57-4fb0-b8bc-84dc37296c1e');

        self::assertSame('8311db73-de57-4fb0-b8bc-84dc37296c1e', (string) $eventId);
    }

    public function testFromStringWithInvalidUuid(): void
    {
        $this->expectException(AssertionFailed::class);
        EventId::fromString('invalid-uuid');
    }

    public function testGenerate(): void
    {
        self::assertInstanceOf(EventId::class, EventId::generate());
    }
}
