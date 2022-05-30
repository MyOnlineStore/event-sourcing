<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateFactory;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;

final class InMemoryAggregateRepository implements AggregateRepository
{
    /** @var array<string, AggregateRoot> */
    private array $aggregates = [];

    /**
     * @param class-string<AggregateRoot> $aggregateName
     */
    public function __construct(
        private AggregateFactory $aggregateFactory,
        private string $aggregateName
    ) {
    }

    public function load(AggregateRootId $aggregateRootId): AggregateRoot
    {
        if (!isset($this->aggregates[$aggregateRootId->toString()])) {
            $this->aggregates[$aggregateRootId->toString()] = $this->aggregateFactory->reconstituteFromHistory(
                $this->aggregateName,
                $aggregateRootId,
                new Stream([], new StreamMetadata([]))
            );
        }

        return clone $this->aggregates[$aggregateRootId->toString()];
    }

    public function save(AggregateRoot $aggregateRoot): void
    {
        $this->aggregates[$aggregateRoot->getAggregateRootId()->toString()] = $aggregateRoot;
    }
}
