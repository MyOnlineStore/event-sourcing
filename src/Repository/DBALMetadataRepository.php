<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Exception\EncodingFailed;
use MyOnlineStore\EventSourcing\Service\Encoder;

final class DBALMetadataRepository implements MetadataRepository
{
    private Connection $connection;
    private Encoder $jsonEncoder;

    public function __construct(Connection $connection, Encoder $jsonEncoder)
    {
        $this->connection = $connection;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @throws Exception
     * @throws EncodingFailed
     */
    public function load(string $streamName, AggregateRootId $aggregateRootId): StreamMetadata
    {
        /** @psalm-var array{metadata: string}|false $result */
        $result = $this->connection->fetchAssociative(
            'SELECT metadata FROM ' . $streamName . '_metadata WHERE aggregate_id = ?',
            [$aggregateRootId->toString()],
            ['string']
        );

        /** @psalm-var array<string, string> $metadata */
        $metadata = $result ? (array) $this->jsonEncoder->decode($result['metadata']) : [];

        return new StreamMetadata($metadata);
    }

    /**
     * @throws Exception
     */
    public function remove(string $streamName, AggregateRootId $aggregateRootId): void
    {
        $this->connection->executeStatement(
            'DELETE FROM ' . $streamName . '_metadata WHERE aggregate_id = ?',
            [$aggregateRootId->toString()],
            ['string']
        );
    }

    /**
     * @throws Exception
     * @throws EncodingFailed
     */
    public function save(string $streamName, AggregateRootId $aggregateRootId, StreamMetadata $metadata): void
    {
        $this->connection->executeStatement(
            'INSERT INTO ' . $streamName . '_metadata (aggregate_id, metadata) VALUES (:aggregate_id, :metadata)
            ON CONFLICT (aggregate_id) DO UPDATE SET metadata = :metadata',
            [
                'aggregate_id' => $aggregateRootId->toString(),
                'metadata' => $this->jsonEncoder->encode($metadata->getMetadata()),
            ],
            [
                'aggregate_id' => 'string',
                'metadata' => 'string',
            ]
        );
    }
}
