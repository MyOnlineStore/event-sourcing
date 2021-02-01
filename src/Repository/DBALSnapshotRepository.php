<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Aggregate\Snapshot;
use MyOnlineStore\EventSourcing\Exception\SnapshotNotFound;

final class DBALSnapshotRepository implements SnapshotRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws SnapshotNotFound
     * @throws Exception
     */
    public function load(string $streamName, AggregateRootId $aggregateRootId): Snapshot
    {
        $result = $this->connection->fetchAssociative(
            'SELECT version, state FROM ' . $streamName . '_snapshot WHERE aggregate_id = ?',
            [(string) $aggregateRootId],
            ['string']
        );

        if (!$result) {
            throw SnapshotNotFound::withAggregateRootId($aggregateRootId);
        }

        return new Snapshot($aggregateRootId, (int) $result['version'], (string) $result['state']);
    }

    /**
     * @throws Exception
     */
    public function save(string $streamName, Snapshot $snapshot): void
    {
        $this->connection->executeStatement(
            'INSERT INTO ' . $streamName . '_snapshot (aggregate_id, version, state)
            VALUES (:aggregate_id, :version, :state)
            ON CONFLICT (aggregate_id) DO UPDATE SET version = :version, state = :state',
            [
                'aggregate_id' => (string) $snapshot->getAggregateRootId(),
                'version' => $snapshot->getAggregateVersion(),
                'state' => $snapshot->getState(),
            ],
            [
                'aggregate_id' => 'string',
                'version' => 'integer',
                'state' => 'string',
            ]
        );
    }
}
