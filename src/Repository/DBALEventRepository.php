<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\EventConverter;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Exception\EncodingFailed;
use MyOnlineStore\EventSourcing\Service\Encoder;

final class DBALEventRepository implements EventRepository
{
    /** @var Connection */
    private $connection;

    /** @var EventConverter */
    private $eventConverter;

    /** @var Encoder */
    private $jsonEncoder;

    public function __construct(
        Connection $connection,
        Encoder $jsonEncoder,
        EventConverter $eventConverter
    ) {
        $this->connection = $connection;
        $this->jsonEncoder = $jsonEncoder;
        $this->eventConverter = $eventConverter;
    }

    /**
     * @throws ConnectionException
     * @throws DBALException
     * @throws EncodingFailed
     */
    public function appendTo(string $streamName, AggregateRootId $aggregateRootId, Stream $eventStream): void
    {
        $eventCount = $eventStream->count();

        if (0 === $eventCount) {
            return;
        }

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
        $metadata = $eventStream->getMetadata();

        foreach ($eventStream as $event) {
            $eventData = $this->eventConverter->convertToArray($event, $metadata);

            $data[] = $eventData['event_id'];
            $data[] = \get_class($event);
            $data[] = $eventData['aggregate_id'];
            $data[] = $this->jsonEncoder->encode($eventData['payload']);
            $data[] = $this->jsonEncoder->encode($eventData['metadata']);
            $data[] = $eventData['created_at'];
            $data[] = $eventData['version'];
        }

        // @todo Add concurrency resolver

        $this->connection->beginTransaction();
        $this->connection->prepare($insertStatement)->execute($data);
        $this->connection->commit();
    }

    /**
     * @throws DBALException
     * @throws EncodingFailed
     */
    public function load(string $streamName, AggregateRootId $aggregateRootId, StreamMetadata $metadata): Stream
    {
        $result = $this->connection->executeQuery(
            'SELECT * FROM '.$streamName.' WHERE aggregate_id = ? ORDER BY version ASC',
            [(string) $aggregateRootId]
        );

        $events = [];
        while (false !== $eventData = $result->fetch()) {
            $events[] = $this->eventConverter->createFromArray(
                $eventData['event_name'],
                [
                    'event_id' => $eventData['event_id'],
                    'aggregate_id' => $eventData['aggregate_id'],
                    'payload' => $this->jsonEncoder->decode($eventData['payload']),
                    'metadata' => $this->jsonEncoder->decode($eventData['metadata']),
                    'created_at' => $eventData['created_at'],
                    'version' => $eventData['version'],
                ],
                $metadata
            );
        }

        return new Stream($events, $metadata);
    }
}
