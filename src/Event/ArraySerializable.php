<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

interface ArraySerializable extends Event
{
    /**
     * @param mixed[] $data
     *
     * @psalm-param array{
     *     event_id: string,
     *     aggregate_id: string,
     *     created_at: string,
     *     metadata: array<array-key, mixed>,
     *     payload: array<array-key, mixed>,
     *     version: int
     * } $data
     */
    public static function fromArray(array $data): Event;

    /**
     * @return mixed[] Must return an array of scalars/sub-arrays of scalars
     *
     * @psalm-return array{
     *     event_id: string,
     *     aggregate_id: string,
     *     created_at: string,
     *     metadata: array<array-key, mixed>,
     *     payload: array<array-key, mixed>,
     *     version: int
     * }
     */
    public function toArray(): array;
}
