<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

interface EventConverter
{
    /**
     * @return mixed[]
     *
     * @psalm-return array{
     *     aggregate_id: string,
     *     created_at: string,
     *     event_id: string,
     *     metadata: array<array-key, mixed>,
     *     payload: array<array-key, mixed>,
     *     version: int
     * }
     */
    public function convertToArray(Event $event, StreamMetadata $streamMetadata): array;

    /**
     * @param mixed[] $data
     *
     * @psalm-param array{
     *     aggregate_id: string,
     *     created_at: string,
     *     event_id: string,
     *     metadata: array<array-key, mixed>,
     *     payload: array<array-key, mixed>,
     *     version: int
     * } $data
     * @psalm-param class-string<Event> $eventName
     */
    public function createFromArray(string $eventName, array $data, StreamMetadata $streamMetadata): Event;
}
