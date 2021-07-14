<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Exception;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;

final class ReadModelNotFound extends EventSourcingException
{
    public static function withAggregateRootId(AggregateRootId $aggregateRootId, string $readModel = 'ReadModel'): self
    {
        return new self(\sprintf('%s not found for aggregate "%s"', $readModel, $aggregateRootId->toString()));
    }
}
