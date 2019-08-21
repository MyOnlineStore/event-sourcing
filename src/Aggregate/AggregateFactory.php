<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

use MyOnlineStore\EventSourcing\Event\Event;

interface AggregateFactory
{
    /**
     * @param Event[] $events
     */
    public function reconstituteFromHistory(
        string $aggregateName,
        AggregateRootId $aggregateRootId,
        array $events
    ): AggregateRoot;
}
