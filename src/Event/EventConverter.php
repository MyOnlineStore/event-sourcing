<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

interface EventConverter
{
    /**
     * @return array<string, scalar|null>
     *
     * @psalm-return array{
     *     aggregate_id: string,
     *     created_at: string,
     *     event_id: string,
     *     metadata: array<string, scalar|null>,
     *     payload: array<string, scalar|null>,
     *     version: int
     * }
     */
    public function convertToArray(Event $event, StreamMetadata $streamMetadata): array;

    /**
     * @param array<string, scalar|null> $data
     *
     * @psalm-param array{
     *     aggregate_id: string,
     *     created_at: string,
     *     event_id: string,
     *     metadata: array<string, scalar|null>,
     *     payload: array<string, scalar|null>,
     *     version: int
     * } $data
     * @psalm-param class-string<Event> $eventName
     */
    public function createFromArray(string $eventName, array $data, StreamMetadata $streamMetadata): Event;
}
