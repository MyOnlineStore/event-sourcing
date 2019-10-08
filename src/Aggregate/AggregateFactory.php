<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

use MyOnlineStore\EventSourcing\Event\Stream;

interface AggregateFactory
{
    /**
     * @psalm-param class-string<AggregateRoot> $aggregateName
     */
    public function reconstituteFromHistory(
        string $aggregateName,
        AggregateRootId $aggregateRootId,
        Stream $eventStream
    ): AggregateRoot;
}
