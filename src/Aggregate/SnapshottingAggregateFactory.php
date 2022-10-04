<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

use MyOnlineStore\EventSourcing\Event\Stream;

/** @template T of SnapshottingAggregateRoot */
interface SnapshottingAggregateFactory
{
    /**
     * @param class-string<T> $aggregateName
     *
     * @return T
     */
    public function reconstituteFromSnapshotAndHistory(
        string $aggregateName,
        Snapshot $snapshot,
        Stream $eventStream,
    ): AggregateRoot;
}
