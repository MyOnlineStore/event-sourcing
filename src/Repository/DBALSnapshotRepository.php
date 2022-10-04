<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Aggregate\Snapshot;
use MyOnlineStore\EventSourcing\Exception\SnapshotNotFound;

final class DBALSnapshotRepository implements SnapshotRepository
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @throws SnapshotNotFound
     * @throws Exception
     */
    public function load(string $streamName, AggregateRootId $aggregateRootId): Snapshot
    {
        $result = $this->connection->fetchAssociative(
            'SELECT version, state FROM ' . $streamName . '_snapshot WHERE aggregate_id = ?',
            [$aggregateRootId->toString()],
            [Types::STRING],
        );

        if (!$result) {
            throw SnapshotNotFound::withAggregateRootId($aggregateRootId);
        }

        return new Snapshot($aggregateRootId, (int) $result['version'], (string) $result['state']);
    }

    /** @throws Exception */
    public function save(string $streamName, Snapshot $snapshot): void
    {
        $this->connection->executeStatement(
            'INSERT INTO ' . $streamName . '_snapshot (aggregate_id, version, state)
            VALUES (:aggregate_id, :version, :state)
            ON CONFLICT (aggregate_id) DO UPDATE SET version = :version, state = :state',
            [
                'aggregate_id' => $snapshot->getAggregateRootId()->toString(),
                'version' => $snapshot->getAggregateVersion(),
                'state' => $snapshot->getState(),
            ],
            [
                'aggregate_id' => Types::STRING,
                'version' => Types::INTEGER,
                'state' => Types::STRING,
            ],
        );
    }
}
