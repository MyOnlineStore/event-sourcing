<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

interface ArraySerializable extends Event
{
    /**
     * @param array{
     *     eventId: string,
     *     aggregateId: string,
     *     createdAt: string,
     *     metadata: array<string, scalar|array|null>,
     *     payload: array<string, scalar|array|null>,
     *     version: int
     * } $data
     */
    public static function fromArray(array $data): Event;

    /**
     * @return array{
     *     eventId: string,
     *     aggregateId: string,
     *     createdAt: string,
     *     metadata: array<string, scalar|array|null>,
     *     payload: array<string, scalar|array|null>,
     *     version: int
     * }
     */
    public function toArray(): array;
}
