<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Repository\EventRepository;
use MyOnlineStore\EventSourcing\Repository\EventRepositoryDecorator;
use PHPUnit\Framework\TestCase;

final class EventRepositoryDecoratorTest extends TestCase
{
    /** @var AggregateRootId */
    private $aggregateId;

    /** @var EventRepositoryDecorator */
    private $decorator;

    /** @var EventRepository */
    private $innerRepository;

    /** @var Stream */
    private $stream;

    /** @var StreamMetadata */
    private $streamMetadata;

    /** @var string */
    private $streamName;

    protected function setUp(): void
    {
        $this->decorator = new class(
            $this->innerRepository = $this->createMock(EventRepository::class)
        ) extends EventRepositoryDecorator {
        };

        $this->streamName = 'event_stream';
        $this->aggregateId = $this->createMock(AggregateRootId::class);
        $this->streamMetadata = new StreamMetadata([]);
        $this->stream = new Stream([], $this->streamMetadata);
    }

    public function testAppendTo(): void
    {
        $this->innerRepository->expects(self::once())
            ->method('appendTo')
            ->with($this->streamName, $this->aggregateId, $this->stream);

        $this->decorator->appendTo($this->streamName, $this->aggregateId, $this->stream);
    }

    public function testLoad(): void
    {
        $this->innerRepository->expects(self::once())
            ->method('load')
            ->with($this->streamName, $this->aggregateId, $this->streamMetadata)
            ->willReturn($this->stream);

        self::assertSame(
            $this->stream,
            $this->decorator->load($this->streamName, $this->aggregateId, $this->streamMetadata)
        );
    }
}
