<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateFactory;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use PHPUnit\Framework\Assert;

/**
 * @template T of AggregateRoot
 * @implements AggregateRepository<T>
 */
final class InMemoryAggregateRepository implements AggregateRepository
{
    /** @var array<string, T> */
    private array $aggregates = [];

    /**
     * @param AggregateFactory<T> $aggregateFactory
     * @param class-string<T>     $aggregateName
     */
    public function __construct(
        private readonly AggregateFactory $aggregateFactory,
        private readonly string $aggregateName,
    ) {
    }

    public function load(AggregateRootId $aggregateRootId): AggregateRoot
    {
        if (!isset($this->aggregates[$aggregateRootId->toString()])) {
            $this->aggregates[$aggregateRootId->toString()] = $this->aggregateFactory->reconstituteFromHistory(
                $this->aggregateName,
                $aggregateRootId,
                new Stream([], new StreamMetadata([])),
            );
        }

        return clone $this->aggregates[$aggregateRootId->toString()];
    }

    public function save(AggregateRoot $aggregateRoot): void
    {
        $this->aggregates[$aggregateRoot->getAggregateRootId()->toString()] = $aggregateRoot;
    }

    public function assertIsEmpty(AggregateRootId $aggregateRootId): void
    {
        Assert::assertCount(0, $this->aggregates);
    }

    public function assertContains(AggregateRootId $aggregateRootId): void
    {
        Assert::assertArrayHasKey($aggregateRootId->toString(), $this->aggregates);
    }

    public function assertNotContains(AggregateRootId $aggregateRootId): void
    {
        Assert::assertArrayNotHasKey($aggregateRootId->toString(), $this->aggregates);
    }
}
