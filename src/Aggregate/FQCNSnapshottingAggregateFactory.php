<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Service\Assert;

final class FQCNSnapshottingAggregateFactory implements SnapshottingAggregateFactory
{
    public function reconstituteFromSnapshotAndHistory(
        string $aggregateName,
        Snapshot $snapshot,
        Stream $eventStream
    ): AggregateRoot {
        Assert::classExists($aggregateName);
        /** @psalm-suppress DocblockTypeContradiction */
        Assert::subclassOf($aggregateName, SnapshottingAggregateRoot::class);

        /** @var SnapshottingAggregateRoot $aggregateName */

        return $aggregateName::reconstituteFromSnapshotAndHistory($snapshot, $eventStream);
    }
}
