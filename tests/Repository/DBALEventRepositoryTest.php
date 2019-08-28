<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\Driver\Statement;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Event\EventConverter;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Repository\DBALEventRepository;
use MyOnlineStore\EventSourcing\Service\Encoder;
use PHPUnit\Framework\TestCase;

final class DBALEventRepositoryTest extends TestCase
{
    /** @var Connection */
    private $connection;

    /** @var EventConverter */
    private $eventConverter;

    /** @var Encoder */
    private $jsonEncoder;

    /** @var DBALEventRepository */
    private $repository;

    protected function setUp(): void
    {
        $this->repository = new DBALEventRepository(
            $this->connection = $this->createMock(Connection::class),
            $this->jsonEncoder = $this->createMock(Encoder::class),
            $this->eventConverter = $this->createMock(EventConverter::class)
        );
    }

    public function testAppendTo(): void
    {
        $streamName = 'stream';
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $events = [
            $event1 = $this->createMock(Event::class),
            $event2 = $this->createMock(Event::class),
        ];

        $this->eventConverter->expects(self::exactly(2))
            ->method('convertToArray')
            ->willReturnOnConsecutiveCalls(
                [
                    'event_id' => 'event1a',
                    'aggregate_id' => 'event1f',
                    'payload' => 'event1b',
                    'metadata' => 'event1c',
                    'created_at' => 'event1d',
                    'version' => 'event1e',
                ],
                [
                    'event_id' => 'event2a',
                    'aggregate_id' => 'event2f',
                    'payload' => 'event2b',
                    'metadata' => 'event2c',
                    'created_at' => 'event2d',
                    'version' => 'event2e',
                ]
            );

        $this->jsonEncoder->expects(self::exactly(4))
            ->method('encode')
            ->withConsecutive(
                ['event1b'],
                ['event1c'],
                ['event2b'],
                ['event2c']
            )
            ->willReturnOnConsecutiveCalls(
                'event1b_json',
                'event1c_json',
                'event2b_json',
                'event2c_json'
            );

        $this->connection->expects(self::once())->method('beginTransaction');
        $this->connection->expects(self::once())
            ->method('prepare')
            ->with(self::isType('string'))
            ->willReturn($statement = $this->createMock(Statement::class));
        $statement->expects(self::once())
            ->method('execute')
            ->with(
                [
                    'event1a',
                    \get_class($event1),
                    'event1f',
                    'event1b_json',
                    'event1c_json',
                    'event1d',
                    'event1e',
                    'event2a',
                    \get_class($event2),
                    'event2f',
                    'event2b_json',
                    'event2c_json',
                    'event2d',
                    'event2e',
                ]
            );
        $this->connection->expects(self::once())->method('commit');

        $this->repository->appendTo($streamName, $aggregateRootId, new Stream($events, new StreamMetadata([])));
    }

    public function testAppendToDoesNothingIfNoEventsToAppend(): void
    {
        $aggregateRootId = $this->createMock(AggregateRootId::class);

        $this->eventConverter->expects(self::never())->method('convertToArray');
        $this->connection->expects(self::never())->method('beginTransaction');

        $this->repository->appendTo('stream', $aggregateRootId, new Stream([], new StreamMetadata([])));
    }

    public function testLoad(): void
    {
        $repository = $this->getMockBuilder(DBALEventRepository::class)
            ->setConstructorArgs(
                [
                    $this->connection,
                    $this->jsonEncoder,
                    $this->eventConverter,
                ]
            )
            ->onlyMethods(['loadMetadata'])
            ->getMock();

        $streamName = 'event_stream';
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $aggregateRootId->method('__toString')->willReturn('agg-id');

        $repository->expects(self::once())
            ->method('loadMetadata')
            ->with($streamName, $aggregateRootId)
            ->willReturn($metadata = new StreamMetadata([]));

        $this->connection->expects(self::once())
            ->method('executeQuery')
            ->with('SELECT * FROM '.$streamName.' WHERE aggregate_id = ? ORDER BY version ASC', ['agg-id'])
            ->willReturn($result = $this->createMock(ResultStatement::class));

        $result->expects(self::exactly(3))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(
                [
                    'event_name' => 'event1',
                    'event_id' => 'id1',
                    'aggregate_id' => 'agg1',
                    'payload' => 'pay1_json',
                    'metadata' => 'met1_json',
                    'created_at' => 'ts1',
                    'version' => 'v1',
                ],
                [
                    'event_name' => 'event2',
                    'event_id' => 'id2',
                    'aggregate_id' => 'agg2',
                    'payload' => 'pay2_json',
                    'metadata' => 'met2_json',
                    'created_at' => 'ts2',
                    'version' => 'v2',
                ],
                false
            );

        $this->jsonEncoder->expects(self::exactly(4))
            ->method('decode')
            ->withConsecutive(
                ['pay1_json'],
                ['met1_json'],
                ['pay2_json'],
                ['met2_json'],
            )
            ->willReturnOnConsecutiveCalls(
                'pay1',
                'met1',
                'pay2',
                'met2',
            );

        $this->eventConverter->expects(self::exactly(2))
            ->method('createFromArray')
            ->withConsecutive(
                [
                    'event1',
                    [
                        'event_id' => 'id1',
                        'aggregate_id' => 'agg1',
                        'payload' => 'pay1',
                        'metadata' => 'met1',
                        'created_at' => 'ts1',
                        'version' => 'v1',
                    ],
                    $metadata,
                ],
                [
                    'event2',
                    [
                        'event_id' => 'id2',
                        'aggregate_id' => 'agg2',
                        'payload' => 'pay2',
                        'metadata' => 'met2',
                        'created_at' => 'ts2',
                        'version' => 'v2',
                    ],
                    $metadata,
                ],
            )
            ->willReturnOnConsecutiveCalls(
                $event1 = $this->createMock(Event::class),
                $event2 = $this->createMock(Event::class)
            );

        self::assertEquals(new Stream([$event1, $event2], $metadata), $repository->load($streamName, $aggregateRootId));
    }

    public function testLoadMetadata(): void
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
            $this->repository->loadMetadata($streamName, $aggregateRootId)
        );
    }

    public function testLoadMetadataNotFound(): void
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
            $this->repository->loadMetadata($streamName, $aggregateRootId)
        );
    }

    public function testUpdateMetadata(): void
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

        $this->repository->updateMetadata($streamName, $aggregateRootId, $metadata);
    }
}
