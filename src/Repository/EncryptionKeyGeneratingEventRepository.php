<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Repository;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\Stream;
use MyOnlineStore\EventSourcing\Service\KeyGenerator;

/**
 * @final
 */
class EncryptionKeyGeneratingEventRepository extends EventRepositoryDecorator
{
    /** @var KeyGenerator */
    private $keyGenerator;

    public function __construct(EventRepository $innerRepository, KeyGenerator $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;

        parent::__construct($innerRepository);
    }

    public function appendTo(string $streamName, AggregateRootId $aggregateRootId, Stream $eventStream): void
    {
        $metadata = $eventStream->getMetadata();

        if (!$metadata->hasEncryptionKey()) {
            $metadata = $metadata->withEncryptionKey($this->keyGenerator->generate());
            $eventStream = $eventStream->withMetadata($metadata);

            $this->updateMetadata(
                $streamName,
                $aggregateRootId,
                $metadata
            );
        }

        parent::appendTo($streamName, $aggregateRootId, $eventStream);
    }
}
