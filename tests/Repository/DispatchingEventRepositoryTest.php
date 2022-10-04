<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Repository\DispatchingEventRepository;
use MyOnlineStore\EventSourcing\Repository\EventRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

final class DispatchingEventRepositoryTest extends TestCase
{
    /** @var EventDispatcherInterface&MockObject */
    private EventDispatcherInterface $dispatcher;

    /** @var EventRepository&MockObject */
    private EventRepository $innerRepository;

    private DispatchingEventRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new DispatchingEventRepository(
            $this->dispatcher = $this->createMock(EventDispatcherInterface::class),
            $this->innerRepository = $this->createMock(EventRepository::class),
        );
    }

    public function testDispatchesEventsAfterCallingInnerRepository(): void
    {
        $streamName = 'foo';
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $eventStream = new Stream(
            [
                $event1 = $this->createMock(Event::class),
                $event2 = $this->createMock(Event::class),
            ],
            new StreamMetadata([]),
        );

        $this->innerRepository->expects(self::once())
            ->method('appendTo')
            ->with($streamName, $aggregateRootId, $eventStream);

        $this->dispatcher->expects(self::exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$event1],
                [$event2],
            );

        $this->repository->appendTo($streamName, $aggregateRootId, $eventStream);
    }

    public function testLoadCallsInnerRepositoryWithoutDispatching(): void
    {
        $streamName = 'foo';
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $eventStream = new Stream(
            [
                $event = $this->createMock(Event::class),
                $event,
            ],
            $metadata = new StreamMetadata([]),
        );

        $this->innerRepository->expects(self::once())
            ->method('load')
            ->with($streamName, $aggregateRootId, $metadata)
            ->willReturn($eventStream);

        $this->dispatcher->expects(self::never())->method('dispatch');

        self::assertSame($eventStream, $this->repository->load($streamName, $aggregateRootId, $metadata));
    }

    public function testLoadAfterVersionCallsInnerRepositoryWithoutDispatching(): void
    {
        $streamName = 'foo';
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $version = 12;
        $eventStream = new Stream(
            [
                $event = $this->createMock(Event::class),
                $event,
            ],
            $metadata = new StreamMetadata([]),
        );

        $this->innerRepository->expects(self::once())
            ->method('loadAfterVersion')
            ->with($streamName, $aggregateRootId, $version, $metadata)
            ->willReturn($eventStream);

        $this->dispatcher->expects(self::never())->method('dispatch');

        self::assertSame(
            $eventStream,
            $this->repository->loadAfterVersion($streamName, $aggregateRootId, $version, $metadata),
        );
    }
}
