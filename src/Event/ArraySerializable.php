<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

interface ArraySerializable extends Event
{
    /**
     * @param mixed[] $data
     */
    public static function fromArray(array $data): Event;

    /**
     * @return mixed[] Must return an array of scalars/sub-arrays of scalars
     */
    public function toArray(): array;
}
