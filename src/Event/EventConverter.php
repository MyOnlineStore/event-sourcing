<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

interface EventConverter
{
    /**
     * @return array{
     *     aggregateId: string,
     *     createdAt: string,
     *     eventId: string,
     *     metadata: array<string, scalar|array|null>,
     *     payload: array<string, scalar|array|null>,
     *     version: int
     * }
     */
    public function convertToArray(Event $event, StreamMetadata $streamMetadata): array;

    /**
     * @param class-string<Event> $eventName
     * @param array{
     *     aggregateId: string,
     *     createdAt: string,
     *     eventId: string,
     *     metadata: array<string, scalar|array|null>,
     *     payload: array<string, scalar|array|null>,
     *     version: int
     * } $data
     */
    public function createFromArray(string $eventName, array $data, StreamMetadata $streamMetadata): Event;
}
