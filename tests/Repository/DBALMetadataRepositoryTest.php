<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Repository\DBALMetadataRepository;
use MyOnlineStore\EventSourcing\Service\Encoder;
use PHPUnit\Framework\TestCase;

final class DBALMetadataRepositoryTest extends TestCase
{
    /** @var Connection */
    private $connection;

    /** @var Encoder */
    private $jsonEncoder;

    /** @var DBALMetadataRepository */
    private $repository;

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
        $aggregateRootId->method('__toString')->willReturn('agg-id');

        $this->connection->expects(self::once())
            ->method('executeQuery')
            ->with('SELECT metadata FROM '.$streamName.'_metadata WHERE aggregate_id = ?', ['agg-id'])
            ->willReturn($resultStatement = $this->createMock(ResultStatement::class));

        $resultStatement->expects(self::once())
            ->method('fetch')
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
        $aggregateRootId->method('__toString')->willReturn('agg-id');

        $this->connection->expects(self::once())
            ->method('executeQuery')
            ->with('SELECT metadata FROM '.$streamName.'_metadata WHERE aggregate_id = ?', ['agg-id'])
            ->willReturn($resultStatement = $this->createMock(ResultStatement::class));

        $resultStatement->expects(self::once())
            ->method('fetch')
            ->willReturn(false);

        $this->jsonEncoder->expects(self::never())->method('decode');

        self::assertEquals(
            new StreamMetadata([]),
            $this->repository->load($streamName, $aggregateRootId)
        );
    }

    public function testSave(): void
    {
        $streamName = 'stream';
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $aggregateRootId->method('__toString')->willReturn('agg-id');
        $metadata = new StreamMetadata(['foo' => 'bar']);

        $this->jsonEncoder->expects(self::once())
            ->method('encode')
            ->with(['foo' => 'bar'])
            ->willReturn('foobar_json');

        $this->connection->expects(self::once())
            ->method('executeUpdate')
            ->with(
                'INSERT INTO '.$streamName.'_metadata (aggregate_id, metadata) VALUES (:aggregate_id, :metadata)
            ON CONFLICT (aggregate_id) DO UPDATE SET metadata = :metadata',
                [
                    'aggregate_id' => 'agg-id',
                    'metadata' => 'foobar_json',
                ]
            );

        $this->repository->save($streamName, $aggregateRootId, $metadata);
    }
}
