<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

use MyOnlineStore\EventSourcing\Event\Stream;

final class FQCNAggregateFactory implements AggregateFactory
{
    /**
     * @param class-string<T> $aggregateName
     *
     * @return T
     *
     * @template T of AggregateRoot
     */
    public function reconstituteFromHistory(
        string $aggregateName,
        AggregateRootId $aggregateRootId,
        Stream $eventStream
    ): AggregateRoot {
        return $aggregateName::reconstituteFromHistory($aggregateRootId, $eventStream);
    }
}
