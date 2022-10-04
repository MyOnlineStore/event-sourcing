<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

use MyOnlineStore\EventSourcing\Service\Assert;

final class ArraySerializableEventConverter implements EventConverter
{
    /** @inheritDoc */
    public function convertToArray(Event $event, StreamMetadata $streamMetadata): array
    {
        Assert::isInstanceOf($event, ArraySerializable::class);

        return $event->toArray();
    }

    /** @inheritDoc */
    public function createFromArray(string $eventName, array $data, StreamMetadata $streamMetadata): Event
    {
        Assert::classExists($eventName);
        /** @psalm-suppress DocblockTypeContradiction */
        Assert::subclassOf($eventName, ArraySerializable::class);

        /** @var ArraySerializable $eventName */

        return $eventName::fromArray($data);
    }
}
