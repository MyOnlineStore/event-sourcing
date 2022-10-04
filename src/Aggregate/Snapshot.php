<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

final class Snapshot
{
    public function __construct(private AggregateRootId $aggregateRootId, private int $version, private string $state)
    {
    }

    public function getAggregateRootId(): AggregateRootId
    {
        return $this->aggregateRootId;
    }

    public function getAggregateVersion(): int
    {
        return $this->version;
    }

    public function getState(): string
    {
        return $this->state;
    }
}
