<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;

/**
 * @template T of AggregateRoot
 * @implements AggregateRepository<T>
 */
final class RuntimeCachedAggregateRepository implements AggregateRepository
{
    /** @var array<string, T> */
    private array $aggregates = [];

    /** @param AggregateRepository<T> $innerRepository */
    public function __construct(private AggregateRepository $innerRepository)
    {
    }

    public function save(AggregateRoot $aggregateRoot): void
    {
        $this->innerRepository->save($aggregateRoot);
        // @todo When concurrency is handled, a save should invalidate the runtime cache
        $this->aggregates[$aggregateRoot->getAggregateRootId()->toString()] = $aggregateRoot;
    }

    public function load(AggregateRootId $aggregateRootId): AggregateRoot
    {
        $aggregateRootIdString = $aggregateRootId->toString();

        if (!isset($this->aggregates[$aggregateRootIdString])) {
            $this->aggregates[$aggregateRootIdString] = $this->innerRepository->load($aggregateRootId);
        }

        return $this->aggregates[$aggregateRootIdString];
    }
}
