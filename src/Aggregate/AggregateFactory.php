<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

use MyOnlineStore\EventSourcing\Event\Stream;

/** @template T of AggregateRoot */
interface AggregateFactory
{
    /**
     * @param class-string<T> $aggregateName
     *
     * @return T
     */
    public function reconstituteFromHistory(
        string $aggregateName,
        AggregateRootId $aggregateRootId,
        Stream $eventStream,
    ): AggregateRoot;
}
