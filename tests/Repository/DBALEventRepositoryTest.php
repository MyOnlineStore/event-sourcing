<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Event\EventConverter;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Repository\DBALEventRepository;
use MyOnlineStore\EventSourcing\Service\Encoder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DBALEventRepositoryTest extends TestCase
{
    /** @psalm-var Connection&MockObject */
    private Connection $connection;
    /** @psalm-var EventConverter&MockObject */
    private EventConverter $eventConverter;
    /** @psalm-var Encoder&MockObject */
    private Encoder $jsonEncoder;
    private DBALEventRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new DBALEventRepository(
            $this->connection = $this->createMock(Connection::class),
            $this->jsonEncoder = $this->createMock(Encoder::class),
            $this->eventConverter = $this->createMock(EventConverter::class),
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
                    'eventId' => 'event1a',
                    'aggregateId' => 'event1f',
                    'payload' => 'event1b',
                    'metadata' => 'event1c',
                    'createdAt' => 'event1d',
                    'version' => 'event1e',
                ],
                [
                    'eventId' => 'event2a',
                    'aggregateId' => 'event2f',
                    'payload' => 'event2b',
                    'metadata' => 'event2c',
                    'createdAt' => 'event2d',
                    'version' => 'event2e',
                ],
            );

        $this->jsonEncoder->expects(self::exactly(4))
            ->method('encode')
            ->withConsecutive(
                ['event1b'],
                ['event1c'],
                ['event2b'],
                ['event2c'],
            )
            ->willReturnOnConsecutiveCalls(
                'event1b_json',
                'event1c_json',
                'event2b_json',
                'event2c_json',
            );

        $this->connection->expects(self::once())->method('beginTransaction');
        $this->connection->expects(self::once())
            ->method('prepare')
            ->with(self::isType('string'))
            ->willReturn($statement = $this->createMock(Statement::class));
        $statement->expects(self::once())
            ->method('executeStatement')
            ->with(
                [
                    'event1a',
                    $event1::class,
                    'event1f',
                    'event1b_json',
                    'event1c_json',
                    'event1d',
                    'event1e',
                    'event2a',
                    $event2::class,
                    'event2f',
                    'event2b_json',
                    'event2c_json',
                    'event2d',
                    'event2e',
                ],
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
        $streamName = 'event_stream';
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $aggregateRootId->method('__toString')->willReturn('agg-id');
        $metadata = new StreamMetadata([]);

        $this->connection->expects(self::once())
            ->method('fetchAllAssociative')
            ->with('SELECT
                    event_id,
                    event_name,
                    payload,
                    metadata,
                    created_at,
                    version
                FROM ' . $streamName . '
                WHERE aggregate_id = ?
                ORDER BY version ASC', ['agg-id'])
            ->willReturn(
                [
                    [
                        'event_name' => 'event1',
                        'event_id' => 'id1',
                        'payload' => 'pay1_json',
                        'metadata' => 'met1_json',
                        'created_at' => 'ts1',
                        'version' => 'v1',
                    ],
                    [
                        'event_name' => 'event2',
                        'event_id' => 'id2',
                        'payload' => 'pay2_json',
                        'metadata' => 'met2_json',
                        'created_at' => 'ts2',
                        'version' => 'v2',
                    ],
                ],
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
                        'eventId' => 'id1',
                        'aggregateId' => 'agg-id',
                        'payload' => 'pay1',
                        'metadata' => 'met1',
                        'createdAt' => 'ts1',
                        'version' => 'v1',
                    ],
                    $metadata,
                ],
                [
                    'event2',
                    [
                        'eventId' => 'id2',
                        'aggregateId' => 'agg-id',
                        'payload' => 'pay2',
                        'metadata' => 'met2',
                        'createdAt' => 'ts2',
                        'version' => 'v2',
                    ],
                    $metadata,
                ],
            )
            ->willReturnOnConsecutiveCalls(
                $event1 = $this->createMock(Event::class),
                $event2 = $this->createMock(Event::class),
            );

        self::assertEquals(
            new Stream([$event1, $event2], $metadata),
            $this->repository->load($streamName, $aggregateRootId, $metadata),
        );
    }

    public function testLoadAfterVersion(): void
    {
        $streamName = 'event_stream';
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $aggregateRootId->method('__toString')->willReturn('agg-id');
        $version = 12;
        $metadata = new StreamMetadata([]);

        $this->connection->expects(self::once())
            ->method('fetchAllAssociative')
            ->with(
                'SELECT
                    event_id,
                    event_name,
                    payload,
                    metadata,
                    created_at,
                    version
                FROM ' . $streamName . '
                WHERE aggregate_id = ? AND version > ?
                ORDER BY version ASC',
                ['agg-id', 12],
            )
            ->willReturn(
                [
                    [
                        'event_name' => 'event1',
                        'event_id' => 'id1',
                        'payload' => 'pay1_json',
                        'metadata' => 'met1_json',
                        'created_at' => 'ts1',
                        'version' => 'v1',
                    ],
                    [
                        'event_name' => 'event2',
                        'event_id' => 'id2',
                        'payload' => 'pay2_json',
                        'metadata' => 'met2_json',
                        'created_at' => 'ts2',
                        'version' => 'v2',
                    ],
                ],
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
                        'eventId' => 'id1',
                        'aggregateId' => 'agg-id',
                        'payload' => 'pay1',
                        'metadata' => 'met1',
                        'createdAt' => 'ts1',
                        'version' => 'v1',
                    ],
                    $metadata,
                ],
                [
                    'event2',
                    [
                        'eventId' => 'id2',
                        'aggregateId' => 'agg-id',
                        'payload' => 'pay2',
                        'metadata' => 'met2',
                        'createdAt' => 'ts2',
                        'version' => 'v2',
                    ],
                    $metadata,
                ],
            )
            ->willReturnOnConsecutiveCalls(
                $event1 = $this->createMock(Event::class),
                $event2 = $this->createMock(Event::class),
            );

        self::assertEquals(
            new Stream([$event1, $event2], $metadata),
            $this->repository->loadAfterVersion($streamName, $aggregateRootId, $version, $metadata),
        );
    }
}
