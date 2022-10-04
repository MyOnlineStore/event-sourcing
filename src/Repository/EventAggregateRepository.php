<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateFactory;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Stream;

final class EventAggregateRepository implements AggregateRepository
{
    /** @param class-string<AggregateRoot> $aggregateName */
    public function __construct(
        private AggregateFactory $aggregateFactory,
        private EventRepository $eventRepository,
        private MetadataRepository $metadataRepository,
        private string $aggregateName,
        private string $streamName,
    ) {
    }

    public function save(AggregateRoot $aggregateRoot): void
    {
        $aggregateRootId = $aggregateRoot->getAggregateRootId();

        $this->eventRepository->appendTo(
            $this->streamName,
            $aggregateRootId,
            new Stream(
                $aggregateRoot->popRecordedEvents(),
                $this->metadataRepository->load($this->streamName, $aggregateRootId),
            ),
        );
    }

    public function load(AggregateRootId $aggregateRootId): AggregateRoot
    {
        return $this->aggregateFactory->reconstituteFromHistory(
            $this->aggregateName,
            $aggregateRootId,
            $this->eventRepository->load(
                $this->streamName,
                $aggregateRootId,
                $this->metadataRepository->load($this->streamName, $aggregateRootId),
            ),
        );
    }
}
