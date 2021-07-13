<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use Symfony\Component\Messenger\MessageBusInterface;

final class SymfonyMessengerEventRepository implements EventRepository
{
    public function __construct(
        private EventRepository $innerRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function appendTo(string $streamName, AggregateRootId $aggregateRootId, Stream $eventStream): void
    {
        $this->innerRepository->appendTo($streamName, $aggregateRootId, $eventStream);

        foreach ($eventStream as $event) {
            $this->messageBus->dispatch($event);
        }
    }

    public function load(string $streamName, AggregateRootId $aggregateRootId, StreamMetadata $metadata): Stream
    {
        return $this->innerRepository->load($streamName, $aggregateRootId, $metadata);
    }

    public function loadAfterVersion(
        string $streamName,
        AggregateRootId $aggregateRootId,
        int $aggregateVersion,
        StreamMetadata $metadata
    ): Stream {
        return $this->innerRepository->loadAfterVersion($streamName, $aggregateRootId, $aggregateVersion, $metadata);
    }
}
