<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

interface Event
{
    public function getCreatedAt(): \DateTimeImmutable;

    public function getId(): EventId;

    /**
     * @return mixed[]
     */
    public function getMetadata(): array;

    /**
     * @return mixed[]
     */
    public function getPayload(): array;

    public function getVersion(): int;

    /**
     * @param mixed $value
     *
     * @return static
     */
    public function withMetadata(string $key, $value): Event;

    public function withVersion(int $version): Event;
}