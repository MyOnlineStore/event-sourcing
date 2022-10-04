<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
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
    public function __construct(
        private Connection $connection,
        private Encoder $jsonEncoder,
        private EventConverter $eventConverter,
    ) {
    }

    /**
     * @throws ConnectionException
     * @throws EncodingFailed
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

            $data[] = $eventData['eventId'];
            $data[] = $event::class;
            $data[] = $eventData['aggregateId'];
            $data[] = $this->jsonEncoder->encode($eventData['payload']);
            $data[] = $this->jsonEncoder->encode($eventData['metadata']);
            $data[] = $eventData['createdAt'];
            $data[] = $eventData['version'];
        }

        // @todo Add concurrency resolver

        $this->connection->beginTransaction();
        $this->connection->prepare($insertStatement)->executeStatement($data);
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
                ['string'],
            ),
            $metadata,
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
        StreamMetadata $metadata,
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
                ['string', 'integer'],
            ),
            $metadata,
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
        StreamMetadata $metadata,
    ): Stream {
        $events = [];
        $stringAggregateRootId = (string) $aggregateRootId;

        foreach ($result as $eventData) {
            /**
             * @var array{
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
                    'eventId' => $eventData['event_id'],
                    'aggregateId' => $stringAggregateRootId,
                    'payload' => $this->jsonEncoder->decode($eventData['payload']),
                    'metadata' => $this->jsonEncoder->decode($eventData['metadata']),
                    'createdAt' => $eventData['created_at'],
                    'version' => $eventData['version'],
                ],
                $metadata,
            );
        }

        return new Stream($events, $metadata);
    }
}
