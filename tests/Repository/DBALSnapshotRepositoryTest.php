<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Aggregate\Snapshot;
use MyOnlineStore\EventSourcing\Exception\SnapshotNotFound;
use MyOnlineStore\EventSourcing\Repository\DBALSnapshotRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DBALSnapshotRepositoryTest extends TestCase
{
    private Connection&MockObject $connection;
    private DBALSnapshotRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new DBALSnapshotRepository(
            $this->connection = $this->createMock(Connection::class),
        );
    }

    public function testLoad(): void
    {
        $streamName = 'stream';
        $aggregateRootId = AggregateRootId::generate();

        $this->connection->expects(self::once())
            ->method('fetchAssociative')
            ->with(
                'SELECT version, state FROM ' . $streamName . '_snapshot WHERE aggregate_id = ?',
                [$aggregateRootId->toString()],
                [Types::STRING],
            )
            ->willReturn(
                [
                    'version' => 12,
                    'state' => 'aggregate_state',
                ],
            );

        self::assertEquals(
            new Snapshot($aggregateRootId, 12, 'aggregate_state'),
            $this->repository->load($streamName, $aggregateRootId),
        );
    }

    public function testLoadThrowsExceptionIfNotFound(): void
    {
        $this->expectException(SnapshotNotFound::class);

        $streamName = 'stream';
        $aggregateRootId = AggregateRootId::generate();

        $this->connection->expects(self::once())
            ->method('fetchAssociative')
            ->with(
                'SELECT version, state FROM ' . $streamName . '_snapshot WHERE aggregate_id = ?',
                [$aggregateRootId->toString()],
                [Types::STRING],
            )
            ->willReturn(false);

        $this->repository->load($streamName, $aggregateRootId);
    }

    public function testSave(): void
    {
        $streamName = 'stream';
        $aggregateRootId = AggregateRootId::generate();

        $this->connection->expects(self::once())
            ->method('executeStatement')
            ->with(
                'INSERT INTO ' . $streamName . '_snapshot (aggregate_id, version, state)
            VALUES (:aggregate_id, :version, :state)
            ON CONFLICT (aggregate_id) DO UPDATE SET version = :version, state = :state',
                [
                    'aggregate_id' => $aggregateRootId->toString(),
                    'version' => 12,
                    'state' => 'aggregate_state',
                ],
                [
                    'aggregate_id' => Types::STRING,
                    'version' => Types::INTEGER,
                    'state' => Types::STRING,
                ],
            );

        $this->repository->save($streamName, new Snapshot($aggregateRootId, 12, 'aggregate_state'));
    }
}
