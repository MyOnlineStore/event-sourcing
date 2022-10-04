<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Aggregate\SnapshottingAggregateFactory;
use MyOnlineStore\EventSourcing\Aggregate\SnapshottingAggregateRoot;
use MyOnlineStore\EventSourcing\Exception\SnapshotNotFound;

/**
 * @template T of SnapshottingAggregateRoot
 * @implements AggregateRepository<T>
 */
final class SnapshottingAggregateRepository implements AggregateRepository
{
    /**
     * @param AggregateRepository<T> $innerRepository
     * @param class-string<T>        $aggregateName
     */
    public function __construct(
        private readonly AggregateRepository $innerRepository,
        private readonly EventRepository $eventRepository,
        private readonly MetadataRepository $metadataRepository,
        private readonly SnapshotRepository $snapshotRepository,
        private readonly SnapshottingAggregateFactory $aggregateFactory,
        private readonly string $aggregateName,
        private readonly string $streamName,
    ) {
    }

    public function load(AggregateRootId $aggregateRootId): AggregateRoot
    {
        try {
            $snapshot = $this->snapshotRepository->load($this->streamName, $aggregateRootId);

            return $this->aggregateFactory->reconstituteFromSnapshotAndHistory(
                $this->aggregateName,
                $snapshot,
                $this->eventRepository->loadAfterVersion(
                    $this->streamName,
                    $aggregateRootId,
                    $snapshot->getAggregateVersion(),
                    $this->metadataRepository->load($this->streamName, $aggregateRootId),
                ),
            );
        } catch (SnapshotNotFound | \TypeError) {
        }

        return $this->innerRepository->load($aggregateRootId);
    }

    public function save(AggregateRoot $aggregateRoot): void
    {
        $this->innerRepository->save($aggregateRoot);

        /** @psalm-suppress DocblockTypeContradiction */
        if (!$aggregateRoot instanceof SnapshottingAggregateRoot) {
            return;
        }

        $this->snapshotRepository->save($this->streamName, $aggregateRoot->snapshot());
    }
}
