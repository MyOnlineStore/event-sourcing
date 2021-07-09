<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

interface ArraySerializable extends Event
{
    /**
     * @param array<string, scalar|null> $data
     *
     * @psalm-param array{
     *     event_id: string,
     *     aggregate_id: string,
     *     created_at: string,
     *     metadata: array<string, scalar|null>,
     *     payload: array<string, scalar|null>,
     *     version: int
     * } $data
     */
    public static function fromArray(array $data): Event;

    /**
     * @return array<string, scalar|null> Must return an array of scalar|nulls/sub-arrays of scalar|nulls
     *
     * @psalm-return array{
     *     event_id: string,
     *     aggregate_id: string,
     *     created_at: string,
     *     metadata: array<string, scalar|null>,
     *     payload: array<string, scalar|null>,
     *     version: int
     * }
     */
    public function toArray(): array;
}
