<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Aggregate\Snapshot;
use MyOnlineStore\EventSourcing\Exception\SnapshotNotFound;

interface SnapshotRepository
{
    /**
     * @throws SnapshotNotFound
     */
    public function load(string $streamName, AggregateRootId $aggregateRootId): Snapshot;

    public function save(string $streamName, Snapshot $snapshot): void;
}
