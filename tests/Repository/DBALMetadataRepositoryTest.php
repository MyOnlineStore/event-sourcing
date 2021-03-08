<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use Doctrine\DBAL\Connection;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Repository\DBALMetadataRepository;
use MyOnlineStore\EventSourcing\Service\Encoder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DBALMetadataRepositoryTest extends TestCase
{
    /** @var Connection&MockObject */
    private Connection $connection;

    /** @var Encoder&MockObject */
    private Encoder $jsonEncoder;

    private DBALMetadataRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new DBALMetadataRepository(
            $this->connection = $this->createMock(Connection::class),
            $this->jsonEncoder = $this->createMock(Encoder::class),
        );
    }

    public function testLoad(): void
    {
        $streamName = 'stream';
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $aggregateRootId->method('toString')->willReturn('agg-id');

        $this->connection->expects(self::once())
            ->method('fetchAssociative')
            ->with('SELECT metadata FROM stream_metadata WHERE aggregate_id = ?', ['agg-id'], ['string'])
            ->willReturn(
                [
                    'aggregate_id' => 'agg-id',
                    'metadata' => 'met_json',
                ]
            );

        $this->jsonEncoder->expects(self::once())
            ->method('decode')
            ->with('met_json')
            ->willReturn(['meta' => 'data']);

        self::assertEquals(
            new StreamMetadata(['meta' => 'data']),
            $this->repository->load($streamName, $aggregateRootId)
        );
    }

    public function testLoadNotFound(): void
    {
        $streamName = 'stream';
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $aggregateRootId->method('toString')->willReturn('agg-id');

        $this->connection->expects(self::once())
            ->method('fetchAssociative')
            ->with('SELECT metadata FROM stream_metadata WHERE aggregate_id = ?', ['agg-id'], ['string'])
            ->willReturn(false);

        $this->jsonEncoder->expects(self::never())->method('decode');

        self::assertEquals(
            new StreamMetadata([]),
            $this->repository->load($streamName, $aggregateRootId)
        );
    }

    public function testRemove(): void
    {
        $streamName = 'stream';
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $aggregateRootId->method('toString')->willReturn('agg-id');

        $this->connection->expects(self::once())
            ->method('executeStatement')
            ->with(
                'DELETE FROM stream_metadata WHERE aggregate_id = ?',
                ['agg-id'],
                ['string']
            );

        $this->repository->remove($streamName, $aggregateRootId);
    }

    public function testSave(): void
    {
        $streamName = 'stream';
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $aggregateRootId->method('toString')->willReturn('agg-id');
        $metadata = new StreamMetadata(['foo' => 'bar']);

        $this->jsonEncoder->expects(self::once())
            ->method('encode')
            ->with(['foo' => 'bar'])
            ->willReturn('foobar_json');

        $this->connection->expects(self::once())
            ->method('executeStatement')
            ->with(
                'INSERT INTO stream_metadata (aggregate_id, metadata) VALUES (:aggregate_id, :metadata)
            ON CONFLICT (aggregate_id) DO UPDATE SET metadata = :metadata',
                [
                    'aggregate_id' => 'agg-id',
                    'metadata' => 'foobar_json',
                ],
                [
                    'aggregate_id' => 'string',
                    'metadata' => 'string',
                ]
            );

        $this->repository->save($streamName, $aggregateRootId, $metadata);
    }
}
