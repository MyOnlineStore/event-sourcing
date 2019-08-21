<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Event\EventConverter;

final class DBALEventRepository implements EventRepository
{
    /** @var Connection */
    private $connection;

    /** @var EventConverter */
    private $eventConverter;

    public function __construct(Connection $connection, EventConverter $eventConverter)
    {
        $this->connection = $connection;
        $this->eventConverter = $eventConverter;
    }

    /**
     * @param Event[] $events
     *
     * @throws ConnectionException
     * @throws DBALException
     */
    public function appendTo(string $streamName, AggregateRootId $aggregateRootId, array $events): void
    {
        if (empty($events)) {
            return;
        }

        $eventCount = \count($events);
        $insertStatement = 'INSERT INTO '.$streamName.' (
            event_id,
            event_name,
            aggregate_id,
            payload,
            metadata,
            created_at,
            version
        ) VALUES '.\implode(',', \array_fill(0, $eventCount, '(?, ?, ?, ?, ?, ?, ?)'));

        $data = [];
        foreach ($events as $event) {
            $eventData = $this->eventConverter->convertToArray($event);

            $data[] = $eventData['event_id'];
            $data[] = \get_class($event);
            $data[] = (string) $aggregateRootId;
            $data[] = $eventData['payload'];
            $data[] = $eventData['metadata'];
            $data[] = $eventData['created_at'];
            $data[] = $eventData['version'];
        }

        // @todo Add concurrency resolver

        $this->connection->beginTransaction();
        $this->connection->prepare($insertStatement)->execute($data);
        $this->connection->commit();
    }

    /**
     * @return Event[]
     *
     * @throws DBALException
     */
    public function load(string $streamName, AggregateRootId $aggregateRootId): array
    {
        $result = $this->connection->executeQuery(
            'SELECT * FROM '.$streamName.' WHERE aggregate_id = ? ORDER BY version ASC',
            [(string) $aggregateRootId]
        );

        $events = [];
        while (false !== $eventData = $result->fetch()) {
            $events[] = $this->eventConverter->createFromArray($eventData);
        }

        return $events;
    }
}
