<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Tests\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\Driver\Statement;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Event\EventConverter;
use MyOnlineStore\EventSourcing\Repository\DBALEventRepository;
use PHPUnit\Framework\TestCase;

final class DBALEventRepositoryTest extends TestCase
{
    /** @var Connection */
    private $connection;

    /** @var EventConverter */
    private $eventConverter;

    /** @var DBALEventRepository */
    private $repository;

    protected function setUp(): void
    {
        $this->repository = new DBALEventRepository(
            $this->connection = $this->createMock(Connection::class),
            $this->eventConverter = $this->createMock(EventConverter::class)
        );
    }

    public function testAppendTo(): void
    {
        $streamName = 'stream';
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $aggregateRootId->method('__toString')->willReturn('agg_id');
        $events = [
            $event1 = $this->createMock(Event::class),
            $event2 = $this->createMock(Event::class),
        ];

        $this->eventConverter->expects(self::exactly(2))
            ->method('convertToArray')
            ->willReturnOnConsecutiveCalls(
                [
                    'event_id' => 'event1a',
                    'payload' => 'event1b',
                    'metadata' => 'event1c',
                    'created_at' => 'event1d',
                    'version' => 'event1e',
                ],
                [
                    'event_id' => 'event2a',
                    'payload' => 'event2b',
                    'metadata' => 'event2c',
                    'created_at' => 'event2d',
                    'version' => 'event2e',
                ]
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
                    'agg_id',
                    'event1b',
                    'event1c',
                    'event1d',
                    'event1e',
                    'event2a',
                    \get_class($event2),
                    'agg_id',
                    'event2b',
                    'event2c',
                    'event2d',
                    'event2e',
                ]
            );
        $this->connection->expects(self::once())->method('commit');

        $this->repository->appendTo($streamName, $aggregateRootId, $events);
    }

    public function testAppendToDoesNothingIfNoEventsToAppend(): void
    {
        $aggregateRootId = $this->createMock(AggregateRootId::class);

        $this->eventConverter->expects(self::never())->method('convertToArray');
        $this->connection->expects(self::never())->method('beginTransaction');

        $this->repository->appendTo('stream', $aggregateRootId, []);
    }

    public function testLoad(): void
    {
        $streamName = 'event_stream';
        $aggregateRootId = $this->createMock(AggregateRootId::class);
        $aggregateRootId->expects(self::once())
            ->method('__toString')
            ->willReturn('agg-id');

        $this->connection->expects(self::once())
            ->method('executeQuery')
            ->with('SELECT * FROM '.$streamName.' WHERE aggregate_id = ? ORDER BY version ASC', ['agg-id'])
            ->willReturn($result = $this->createMock(ResultStatement::class));

        $result->expects(self::exactly(3))
            ->method('fetch')
            ->willReturnOnConsecutiveCalls(['event1'], ['event2'], false);

        $this->eventConverter->expects(self::exactly(2))
            ->method('createFromArray')
            ->withConsecutive([['event1']], [['event2']])
            ->willReturnOnConsecutiveCalls(
                $event1 = $this->createMock(Event::class),
                $event2 = $this->createMock(Event::class)
            );

        self::assertSame([$event1, $event2], $this->repository->load($streamName, $aggregateRootId));
    }
}
