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

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var EventRepository */
    private $innerRepository;

    /** @var DispatchingEventRepository */
    private $repository;

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
}
