<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Service\Assert;

final class FQCNAggregateFactory implements AggregateFactory
{
    public function reconstituteFromHistory(
        string $aggregateName,
        AggregateRootId $aggregateRootId,
        Stream $eventStream
    ): AggregateRoot {
        Assert::classExists($aggregateName);
        /** @psalm-suppress DocblockTypeContradiction */
        Assert::subclassOf($aggregateName, AggregateRoot::class);

        /** @var AggregateRoot $aggregateName */

        return $aggregateName::reconstituteFromHistory($aggregateRootId, $eventStream);
    }
}
