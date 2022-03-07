<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

interface ArraySerializable extends Event
{
    /**
     * @param array{
     *     event_id: string,
     *     aggregate_id: string,
     *     created_at: string,
     *     metadata: array<string, scalar|array|null>,
     *     payload: array<string, scalar|array|null>,
     *     version: int
     * } $data
     */
    public static function fromArray(array $data): Event;

    /**
     * @return array{
     *     event_id: string,
     *     aggregate_id: string,
     *     created_at: string,
     *     metadata: array<string, scalar|array|null>,
     *     payload: array<string, scalar|array|null>,
     *     version: int
     * }
     */
    public function toArray(): array;
}
