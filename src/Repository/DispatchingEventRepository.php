<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Event;
use Psr\EventDispatcher\EventDispatcherInterface;

final class DispatchingEventRepository implements EventRepository
{
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var EventRepository */
    private $innerRepository;

    public function __construct(EventDispatcherInterface $dispatcher, EventRepository $innerRepository)
    {
        $this->dispatcher = $dispatcher;
        $this->innerRepository = $innerRepository;
    }

    /**
     * @param Event[] $events
     */
    public function appendTo(string $streamName, AggregateRootId $aggregateRootId, array $events): void
    {
        $this->innerRepository->appendTo($streamName, $aggregateRootId, $events);

        foreach ($events as $event) {
            $this->dispatcher->dispatch($event);
        }
    }

    /**
     * @return Event[]
     */
    public function load(string $streamName, AggregateRootId $aggregateRootId): array
    {
        return $this->innerRepository->load($streamName, $aggregateRootId);
    }
}
