<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;
use MyOnlineStore\EventSourcing\Exception\EncodingFailed;
use MyOnlineStore\EventSourcing\Service\Encoder;

final class DBALMetadataRepository implements MetadataRepository
{
    /** @var Connection */
    private $connection;

    /** @var Encoder */
    private $jsonEncoder;

    public function __construct(Connection $connection, Encoder $jsonEncoder)
    {
        $this->connection = $connection;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @throws DBALException
     * @throws EncodingFailed
     */
    public function load(string $streamName, AggregateRootId $aggregateRootId): StreamMetadata
    {
        $result = $this->connection->executeQuery(
            'SELECT metadata FROM '.$streamName.'_metadata WHERE aggregate_id = ?',
            [(string) $aggregateRootId]
        )
            ->fetch();

        return new StreamMetadata($result ? $this->jsonEncoder->decode($result['metadata']) : []);
    }

    /**
     * @throws DBALException
     * @throws EncodingFailed
     */
    public function save(string $streamName, AggregateRootId $aggregateRootId, StreamMetadata $metadata): void
    {
        $this->connection->executeUpdate(
            'INSERT INTO '.$streamName.'_metadata (aggregate_id, metadata) VALUES (:aggregate_id, :metadata)
            ON CONFLICT (aggregate_id) DO UPDATE SET metadata = :metadata',
            [
                'aggregate_id' => (string) $aggregateRootId,
                'metadata' => $this->jsonEncoder->encode($metadata->getMetadata()),
            ]
        );
    }
}
