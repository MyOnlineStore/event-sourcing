<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

use MyOnlineStore\EventSourcing\Event\Stream;

abstract class SnapshottingAggregateRoot extends AggregateRoot
{
    public static function reconstituteFromSnapshotAndHistory(
        Snapshot $snapshot,
        Stream $eventStream,
    ): static {
        $instance = \unserialize(\base64_decode($snapshot->getState()));
        \assert($instance instanceof static);

        foreach ($eventStream as $event) {
            $instance->apply($event);
        }

        return $instance;
    }

    public function snapshot(): Snapshot
    {
        return new Snapshot($this->aggregateRootId, $this->version, \base64_encode(\serialize($this)));
    }
}
