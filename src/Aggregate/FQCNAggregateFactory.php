<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Service\Assertion;

final class FQCNAggregateFactory implements AggregateFactory
{
    /**
     * @param Event[] $events
     */
    public function reconstituteFromHistory(
        string $aggregateName,
        AggregateRootId $aggregateRootId,
        array $events
    ): AggregateRoot {
        Assertion::classExists($aggregateName);
        Assertion::subclassOf($aggregateName, AggregateRoot::class);

        /** @var AggregateRoot $aggregateName */

        return $aggregateName::reconstituteFromHistory($aggregateRootId, $events);
    }
}
