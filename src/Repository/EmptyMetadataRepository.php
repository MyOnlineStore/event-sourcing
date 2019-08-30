<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;

final class EmptyMetadataRepository implements MetadataRepository
{
    public function load(string $streamName, AggregateRootId $aggregateRootId): StreamMetadata
    {
        return new StreamMetadata([]);
    }

    public function save(string $streamName, AggregateRootId $aggregateRootId, StreamMetadata $metadata): void
    {
    }
}
