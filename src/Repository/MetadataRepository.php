<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;

interface MetadataRepository
{
    public function load(string $streamName, AggregateRootId $aggregateRootId): StreamMetadata;

    public function remove(string $streamName, AggregateRootId $aggregateRootId): void;

    public function save(string $streamName, AggregateRootId $aggregateRootId, StreamMetadata $metadata): void;
}
