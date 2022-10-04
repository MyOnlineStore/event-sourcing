<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

use MyOnlineStore\EventSourcing\Event\Stream;

interface SnapshottingAggregateFactory
{
    /**
     * @param class-string<T> $aggregateName
     *
     * @return T
     *
     * @template T of SnapshottingAggregateRoot
     */
    public function reconstituteFromSnapshotAndHistory(
        string $aggregateName,
        Snapshot $snapshot,
        Stream $eventStream,
    ): AggregateRoot;
}
