<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

use MyOnlineStore\EventSourcing\Event\Stream;

interface SnapshottingAggregateFactory
{
    /**
     * @psalm-param class-string<SnapshottingAggregateRoot> $aggregateName
     */
    public function reconstituteFromSnapshotAndHistory(
        string $aggregateName,
        Snapshot $snapshot,
        Stream $eventStream
    ): AggregateRoot;
}
