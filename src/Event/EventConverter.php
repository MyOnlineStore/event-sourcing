<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

interface EventConverter
{
    /**
     * @return mixed[]
     */
    public function convertToArray(Event $event, StreamMetadata $streamMetadata): array;

    /**
     * @param mixed[] $data
     */
    public function createFromArray(string $eventName, array $data, StreamMetadata $streamMetadata): Event;
}
