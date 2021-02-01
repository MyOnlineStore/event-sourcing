<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;

final class RuntimeCachedAggregateRepository implements AggregateRepository
{
    /** @var AggregateRoot[] */
    private array $aggregates = [];
    private AggregateRepository $innerRepository;

    public function __construct(AggregateRepository $innerRepository)
    {
        $this->innerRepository = $innerRepository;
    }

    public function save(AggregateRoot $aggregateRoot): void
    {
        $this->innerRepository->save($aggregateRoot);
        // @todo When concurrency is handled, a save should invalidate the runtime cache
        $this->aggregates[(string) $aggregateRoot->getAggregateRootId()] = $aggregateRoot;
    }

    public function load(AggregateRootId $aggregateRootId): AggregateRoot
    {
        $aggregateRootIdString = (string) $aggregateRootId;

        if (!isset($this->aggregates[$aggregateRootIdString])) {
            $this->aggregates[$aggregateRootIdString] = $this->innerRepository->load($aggregateRootId);
        }

        return $this->aggregates[$aggregateRootIdString];
    }
}
