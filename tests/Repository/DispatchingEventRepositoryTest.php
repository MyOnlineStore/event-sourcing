<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Repository\DispatchingEventRepository;
use MyOnlineStore\EventSourcing\Repository\EventRepository;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

final class DispatchingEventRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private EventDispatcherInterface $dispatcher;
    private EventRepository $innerRepository;
    private DispatchingEventRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new DispatchingEventRepository(
            $this->dispatcher = \Mockery::mock(EventDispatcherInterface::class),
            $this->innerRepository = \Mockery::mock(EventRepository::class)
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

        $this->innerRepository->shouldReceive('appendTo')
            ->once()
            ->with($streamName, $aggregateRootId, $eventStream)
            ->globally()
            ->ordered();

        $this->dispatcher->shouldReceive('dispatch')
            ->once()
            ->with($event1)
            ->globally()
            ->ordered();

        $this->dispatcher->shouldReceive('dispatch')
            ->once()
            ->with($event2)
            ->globally()
            ->ordered();

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

        $this->innerRepository->shouldReceive('load')
            ->once()
            ->with($streamName, $aggregateRootId, $metadata)
            ->andReturn($eventStream);

        $this->dispatcher->shouldNotReceive('dispatch');

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

        $this->innerRepository->shouldReceive('loadAfterVersion')
            ->once()
            ->with($streamName, $aggregateRootId, $version, $metadata)
            ->andReturn($eventStream);

        $this->dispatcher->shouldNotReceive('dispatch');

        self::assertSame(
            $eventStream,
            $this->repository->loadAfterVersion($streamName, $aggregateRootId, $version, $metadata)
        );
    }
}
