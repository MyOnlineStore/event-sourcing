<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

use MyOnlineStore\EventSourcing\Exception\EncodingFailed;
use MyOnlineStore\EventSourcing\Service\Assertion;
use MyOnlineStore\EventSourcing\Service\Encoder;

final class ArraySerializableEventConverter implements EventConverter
{
    /** @var Encoder */
    private $encoder;

    public function __construct(Encoder $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @return mixed[]
     *
     * @throws EncodingFailed
     */
    public function convertToArray(Event $event): array
    {
        Assertion::isInstanceOf($event, ArraySerializable::class);

        /** @var ArraySerializable $event */

        $data = $event->toArray();
        $data['payload'] = $this->encoder->encode($data['payload']);
        $data['metadata'] = $this->encoder->encode($data['metadata']);

        return $data;
    }

    /**
     * @param mixed[] $data
     *
     * @throws EncodingFailed
     */
    public function createFromArray(array $data): Event
    {
        Assertion::keyExists($data, 'event_name');

        $eventName = $data['event_name'];

        Assertion::classExists($eventName);
        Assertion::subclassOf($eventName, ArraySerializable::class);

        $data['payload'] = $this->encoder->decode($data['payload']);
        $data['metadata'] = $this->encoder->decode($data['metadata']);

        /** @var ArraySerializable $eventName */

        return $eventName::fromArray($data);
    }
}
