<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Event;
use MyOnlineStore\EventSourcing\Event\EventConverter;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Exception\EncodingFailed;
use MyOnlineStore\EventSourcing\Service\Encoder;

final class DBALEventRepository implements EventRepository
{
    private Connection $connection;
    private EventConverter $eventConverter;
    private Encoder $jsonEncoder;

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
     * @throws EncodingFailed
     * @throws DriverException
     * @throws Exception
     */
    public function appendTo(string $streamName, AggregateRootId $aggregateRootId, Stream $eventStream): void
    {
        $eventCount = $eventStream->count();

        if (0 === $eventCount) {
            return;
        }

        $insertStatement = 'INSERT INTO ' . $streamName . ' (
            event_id,
            event_name,
            aggregate_id,
            payload,
            metadata,
            created_at,
            version
        ) VALUES ' . \implode(',', \array_fill(0, $eventCount, '(?, ?, ?, ?, ?, ?, ?)'));

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
     * @throws Exception
     * @throws EncodingFailed
     */
    public function load(string $streamName, AggregateRootId $aggregateRootId, StreamMetadata $metadata): Stream
    {
        return $this->parseStream(
            $aggregateRootId,
            $this->connection->fetchAllAssociative(
                'SELECT
                    event_id,
                    event_name,
                    payload,
                    metadata,
                    created_at,
                    version
                FROM ' . $streamName . '
                WHERE aggregate_id = ?
                ORDER BY version ASC',
                [(string) $aggregateRootId],
                ['string']
            ),
            $metadata
        );
    }

    /**
     * @throws Exception
     * @throws EncodingFailed
     */
    public function loadAfterVersion(
        string $streamName,
        AggregateRootId $aggregateRootId,
        int $aggregateVersion,
        StreamMetadata $metadata
    ): Stream {
        return $this->parseStream(
            $aggregateRootId,
            $this->connection->fetchAllAssociative(
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
                [(string) $aggregateRootId, $aggregateVersion],
                ['string', 'integer']
            ),
            $metadata
        );
    }

    /**
     * @param array<int, array<string, mixed>> $result
     *
     * @throws EncodingFailed
     */
    private function parseStream(
        AggregateRootId $aggregateRootId,
        array $result,
        StreamMetadata $metadata
    ): Stream {
        $events = [];
        $stringAggregateRootId = (string) $aggregateRootId;

        foreach ($result as $eventData) {
            /**
             * @psalm-var array{
             *     event_name: class-string<Event>,
             *     aggregate_id: string,
             *     created_at: string,
             *     event_id: string,
             *     metadata: string,
             *     payload: string,
             *     version: int
             * } $eventData
             *
             * @psalm-suppress ArgumentTypeCoercion
             */
            $events[] = $this->eventConverter->createFromArray(
                $eventData['event_name'],
                [
                    'event_id' => $eventData['event_id'],
                    'aggregate_id' => $stringAggregateRootId,
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
