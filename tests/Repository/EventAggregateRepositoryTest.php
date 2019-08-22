<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateFactory;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Repository\EventAggregateRepository;
use MyOnlineStore\EventSourcing\Repository\EventRepository;
use PHPUnit\Framework\TestCase;

final class EventAggregateRepositoryTest extends TestCase
{
    /** @var AggregateFactory */
    private $aggregateFactory;

    /** @var EventRepository */
    private $eventRepository;

    /** @var EventAggregateRepository */
    private $repository;

    /** @var string */
    private $streamName;

    /** @var string */
    private $aggregateName;

    protected function setUp(): void
    {
        $this->repository = new EventAggregateRepository(
            $this->aggregateFactory = $this->createMock(AggregateFactory::class),
            $this->eventRepository = $this->createMock(EventRepository::class),
            $this->aggregateName = 'foo',
            $this->streamName = 'foo_stream'
        );
    }

    public function testLoad(): void
    {
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $aggregateRoot = $this->createMock(AggregateRoot::class);

        $this->eventRepository->expects(self::once())
            ->method('load')
            ->with($this->streamName, $aggregateRootId)
            ->willReturn($events = [$event = $this->createMock(Event::class), $event]);

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

        $this->eventRepository->expects(self::once())
            ->method('appendTo')
            ->with($this->streamName, $aggregateRootId, $events);

        $this->repository->save($aggregateRoot);
    }
}
