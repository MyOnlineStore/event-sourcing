<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

final class Snapshot
{
    private AggregateRootId $aggregateRootId;
    private int $version;
    private string $state;

    public function __construct(AggregateRootId $aggregateRootId, int $version, string $state)
    {
        $this->aggregateRootId = $aggregateRootId;
        $this->version = $version;
        $this->state = $state;
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
