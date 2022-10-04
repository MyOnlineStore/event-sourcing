<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Event;

use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use PHPUnit\Framework\TestCase;

final class StreamTest extends TestCase
{
    public function testConstructor(): void
    {
        $stream = new Stream(
            [
                $event = $this->createMock(Event::class),
                $event,
            ],
            $metadata = new StreamMetadata([]),
        );

        self::assertSame([$event, $event], $stream->getArrayCopy());
        self::assertSame($metadata, $stream->getMetadata());
    }

    public function testWithMetadataCreatesNewCopyWithNewMetadata(): void
    {
        $stream = new Stream([], $metadata = new StreamMetadata([]));
        $newStream = $stream->withMetadata($newMetadata = new StreamMetadata(['foo' => 'bar']));

        self::assertSame($metadata, $stream->getMetadata());
        self::assertSame($newMetadata, $newStream->getMetadata());
    }
}
