<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateFactory;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Repository\EventAggregateRepository;
use MyOnlineStore\EventSourcing\Repository\EventRepository;
use MyOnlineStore\EventSourcing\Repository\MetadataRepository;
use PHPUnit\Framework\TestCase;

final class EventAggregateRepositoryTest extends TestCase
{
    private AggregateFactory $aggregateFactory;
    private EventRepository $eventRepository;
    private EventAggregateRepository $repository;
    private MetadataRepository $metadataRepository;
    private string $streamName;
    private string $aggregateName;

    protected function setUp(): void
    {
        $this->repository = new EventAggregateRepository(
            $this->aggregateFactory = $this->createMock(AggregateFactory::class),
            $this->eventRepository = $this->createMock(EventRepository::class),
            $this->metadataRepository = $this->createMock(MetadataRepository::class),
            $this->aggregateName = 'foo',
            $this->streamName = 'foo_stream'
        );
    }

    public function testLoad(): void
    {
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $aggregateRoot = $this->createMock(AggregateRoot::class);

        $this->metadataRepository->expects(self::once())
            ->method('load')
            ->with($this->streamName, $aggregateRootId)
            ->willReturn($metadata = new StreamMetadata([]));

        $this->eventRepository->expects(self::once())
            ->method('load')
            ->with($this->streamName, $aggregateRootId)
            ->willReturn(
                $events = new Stream(
                    [$event = $this->createMock(Event::class), $event],
                    $metadata,
                )
            );

        $this->aggregateFactory->expects(self::once())
            ->method('reconstituteFromHistory')
            ->with($this->aggregateName, $aggregateRootId, $events)
            ->willReturn($aggregateRoot);

        self::assertSame($aggregateRoot, $this->repository->load($aggregateRootId));
    }

    public function testSave(): void
    {
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $aggregateRoot = $this->createMock(AggregateRoot::class);
        $aggregateRoot->expects(self::once())->method('getAggregateRootId')->willReturn($aggregateRootId);
        $aggregateRoot->expects(self::once())
            ->method('popRecordedEvents')
            ->willReturn($events = [$event = $this->createMock(Event::class), $event]);

        $this->metadataRepository->expects(self::once())
            ->method('load')
            ->with($this->streamName, $aggregateRootId)
            ->willReturn($metadata = new StreamMetadata([]));

        $this->eventRepository->expects(self::once())
            ->method('appendTo')
            ->with($this->streamName, $aggregateRootId, new Stream($events, $metadata));

        $this->repository->save($aggregateRoot);
    }
}
