<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Event;

interface EventRepository
{
    /**
     * @param Event[] $events
     */
    public function appendTo(string $streamName, AggregateRootId $aggregateRootId, array $events): void;

    /**
     * @return Event[]
     */
    public function load(string $streamName, AggregateRootId $aggregateRootId): array;
}
