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
        $events = [
            $event1 = $this->createMock(Event::class),
            $event2 = $this->createMock(Event::class),
        ];

        $this->eventConverter->expects(self::exactly(2))
            ->method('convertToArray')
            ->willReturnOnConsecutiveCalls(
                [
                    'event_id' => 'event1',
                    'payload' => 'event1',
                    'metadata' => 'event1',
                    'created_at' => 'event1',
                    'version' => 'event1',
                ],
                [
                    'event_id' => 'event2',
                    'payload' => 'event2',
                    'metadata' => 'event2',
                    'created_at' => 'event2',
                    'version' => 'event2',
                ]
            );

        $this->connection->expects(self::once())->method('beginTransaction');
        $this->connection->expects(self::once())
            ->method('prepare')
            ->with(self::isType('string'))
            ->willReturn($statement = $this->createMock(Statement::class));
        $statement->expects(self::once())
            ->method('execute')
            ->with(self::isType('array'));
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
