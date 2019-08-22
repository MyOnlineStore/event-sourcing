<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateFactory;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;

final class EventAggregateRepository implements AggregateRepository
{
    /** @var AggregateFactory */
    private $aggregateFactory;

    /** @var EventRepository */
    private $eventRepository;

    /** @var string */
    private $streamName;

    /** @var string */
    private $aggregateName;

    public function __construct(
        AggregateFactory $aggregateFactory,
        EventRepository $eventRepository,
        string $aggregateName,
        string $streamName
    ) {
        $this->aggregateFactory = $aggregateFactory;
        $this->eventRepository = $eventRepository;
        $this->aggregateName = $aggregateName;
        $this->streamName = $streamName;
    }

    public function save(AggregateRoot $aggregateRoot): void
    {
        $this->eventRepository->appendTo(
            $this->streamName,
            $aggregateRoot->getAggregateRootId(),
            $aggregateRoot->popRecordedEvents()
        );
    }

    public function load(AggregateRootId $aggregateRootId): AggregateRoot
    {
        return $this->aggregateFactory->reconstituteFromHistory(
            $this->aggregateName,
            $aggregateRootId,
            $this->eventRepository->load(
                $this->streamName,
                $aggregateRootId
            )
        );
    }
}
