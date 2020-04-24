<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Service\Assert;

abstract class SnapshottingAggregateRoot extends AggregateRoot
{
    public static function reconstituteFromSnapshotAndHistory(
        Snapshot $snapshot,
        Stream $eventStream
    ): SnapshottingAggregateRoot {
        $instance = \unserialize(\base64_decode($snapshot->getState()));

        Assert::isInstanceOf($instance, self::class);

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
