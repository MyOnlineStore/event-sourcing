<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Encryption\KeyGenerator;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Event\StreamMetadata;

final class EncryptionKeyGeneratingEventRepository implements EventRepository
{
    public function __construct(
        private EventRepository $innerRepository,
        private KeyGenerator $keyGenerator,
        private MetadataRepository $metadataRepository,
    ) {
    }

    public function appendTo(string $streamName, AggregateRootId $aggregateRootId, Stream $eventStream): void
    {
        $metadata = $eventStream->getMetadata();

        if (!$metadata->hasEncryptionKey()) {
            $metadata = $metadata->withEncryptionKey($this->keyGenerator->generate());
            $eventStream = $eventStream->withMetadata($metadata);

            $this->metadataRepository->save($streamName, $aggregateRootId, $metadata);
        }

        $this->innerRepository->appendTo($streamName, $aggregateRootId, $eventStream);
    }

    public function load(string $streamName, AggregateRootId $aggregateRootId, StreamMetadata $metadata): Stream
    {
        return $this->innerRepository->load($streamName, $aggregateRootId, $metadata);
    }

    public function loadAfterVersion(
        string $streamName,
        AggregateRootId $aggregateRootId,
        int $aggregateVersion,
        StreamMetadata $metadata,
    ): Stream {
        return $this->innerRepository->loadAfterVersion($streamName, $aggregateRootId, $aggregateVersion, $metadata);
    }
}
