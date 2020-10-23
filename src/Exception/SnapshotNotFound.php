<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Exception;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;

final class SnapshotNotFound extends EventSourcingException
{
    public static function withAggregateRootId(AggregateRootId $aggregateRootId): self
    {
        return new self(\sprintf('Snapshot not found for aggregate "%s"', $aggregateRootId->toString()));
    }
}
