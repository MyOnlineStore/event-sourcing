<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;

interface EventRepository
{
    public function appendTo(string $streamName, AggregateRootId $aggregateRootId, Stream $eventStream): void;

    public function load(string $streamName, AggregateRootId $aggregateRootId, StreamMetadata $metadata): Stream;

    public function loadAfterVersion(
        string $streamName,
        AggregateRootId $aggregateRootId,
        int $aggregateVersion,
        StreamMetadata $metadata,
    ): Stream;
}
