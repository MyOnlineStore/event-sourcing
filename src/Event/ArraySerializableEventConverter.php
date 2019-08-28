<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

use MyOnlineStore\EventSourcing\Service\Assertion;

final class ArraySerializableEventConverter implements EventConverter
{
    /**
     * @return mixed[]
     */
    public function convertToArray(Event $event, StreamMetadata $streamMetadata): array
    {
        Assertion::isInstanceOf($event, ArraySerializable::class);

        /** @var ArraySerializable $event */

        return $event->toArray();
    }

    /**
     * @param mixed[] $data
     */
    public function createFromArray(string $eventName, array $data, StreamMetadata $streamMetadata): Event
    {
        Assertion::classExists($eventName);
        Assertion::subclassOf($eventName, ArraySerializable::class);

        /** @var ArraySerializable $eventName */

        return $eventName::fromArray($data);
    }
}
