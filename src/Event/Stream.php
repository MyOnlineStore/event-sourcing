<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

/** @extends \ArrayObject<array-key, Event> */
final class Stream extends \ArrayObject
{
    /** @param list<Event> $events */
    public function __construct(array $events, private StreamMetadata $metadata)
    {
        parent::__construct($events);
    }

    public function getMetadata(): StreamMetadata
    {
        return $this->metadata;
    }

    public function withMetadata(StreamMetadata $metadata): self
    {
        $copy = clone $this;
        $copy->metadata = $metadata;

        return $copy;
    }
}
