<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;

interface Event
{
    public function getAggregateId(): AggregateRootId;

    public function getCreatedAt(): \DateTimeImmutable;

    public function getId(): EventId;

    /**
     * @return array<string, scalar|null>
     */
    public function getMetadata(): array;

    /**
     * @return array<string, scalar|null>
     */
    public function getPayload(): array;

    public function getVersion(): int;

    /**
     * @psalm-param scalar|null $value
     */
    public function withMetadata(string $key, mixed $value): static;

    public function withVersion(int $version): static;
}
