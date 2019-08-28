<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;

abstract class EventRepositoryDecorator implements EventRepository
{
    /** @var EventRepository */
    private $innerRepository;

    public function __construct(EventRepository $innerRepository)
    {
        $this->innerRepository = $innerRepository;
    }

    public function appendTo(string $streamName, AggregateRootId $aggregateRootId, Stream $eventStream): void
    {
        $this->innerRepository->appendTo($streamName, $aggregateRootId, $eventStream);
    }

    public function load(string $streamName, AggregateRootId $aggregateRootId): Stream
    {
        return $this->innerRepository->load($streamName, $aggregateRootId);
    }

    public function loadMetadata(string $streamName, AggregateRootId $aggregateRootId): StreamMetadata
    {
        return $this->innerRepository->loadMetadata($streamName, $aggregateRootId);
    }

    public function updateMetadata(string $streamName, AggregateRootId $aggregateRootId, StreamMetadata $metadata): void
    {
        $this->innerRepository->updateMetadata($streamName, $aggregateRootId, $metadata);
    }
}
