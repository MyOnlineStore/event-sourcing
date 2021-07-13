<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Repository\EventRepository;
use MyOnlineStore\EventSourcing\Repository\SymfonyMessengerEventRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class SymfonyMessengerEventRepositoryTest extends TestCase
{
    /** @var EventRepository&MockObject */
    private EventRepository $innerRepository;

    /** @var MessageBusInterface&MockObject */
    private MessageBusInterface $messageBus;

    private SymfonyMessengerEventRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new SymfonyMessengerEventRepository(
            $this->innerRepository = $this->createMock(EventRepository::class),
            $this->messageBus = $this->createMock(MessageBusInterface::class)
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
            new StreamMetadata([])
        );

        $this->innerRepository->expects(self::once())
            ->method('appendTo')
            ->with($streamName, $aggregateRootId, $eventStream);

        $this->messageBus->expects(self::exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$event1],
                [$event2],
            )
            ->willReturnOnConsecutiveCalls(
                new Envelope($event1),
                new Envelope($event2),
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
            $metadata = new StreamMetadata([])
        );

        $this->innerRepository->expects(self::once())
            ->method('load')
            ->with($streamName, $aggregateRootId, $metadata)
            ->willReturn($eventStream);

        $this->messageBus->expects(self::never())->method('dispatch');

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
            $metadata = new StreamMetadata([])
        );

        $this->innerRepository->expects(self::once())
            ->method('loadAfterVersion')
            ->with($streamName, $aggregateRootId, $version, $metadata)
            ->willReturn($eventStream);

        $this->messageBus->expects(self::never())->method('dispatch');

        self::assertSame(
            $eventStream,
            $this->repository->loadAfterVersion($streamName, $aggregateRootId, $version, $metadata)
        );
    }
}
