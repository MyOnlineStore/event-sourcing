<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
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

    public function appendTo(string $streamName, AggregateRootId $aggregateRootId, Stream $eventStream): void
    {
        $this->innerRepository->appendTo($streamName, $aggregateRootId, $eventStream);

        foreach ($eventStream as $event) {
            $this->dispatcher->dispatch($event);
        }
    }

    public function load(string $streamName, AggregateRootId $aggregateRootId, StreamMetadata $metadata): Stream
    {
        return $this->innerRepository->load($streamName, $aggregateRootId, $metadata);
    }
}
