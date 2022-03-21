<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Exception\AssertionFailed;
use MyOnlineStore\EventSourcing\Service\Assert;

/**
 * @psalm-immutable
 */
class BaseEvent implements ArraySerializable
{
    private const CREATED_FORMAT = 'Y-m-d H:i:s.u';

    private EventId $id;
    private \DateTimeImmutable $createdAt;
    private int $version;

    /**
     * @param array<string, scalar|array|null> $payload
     * @param array<string, scalar|array|null> $metadata
     */
    final private function __construct(
        private AggregateRootId $aggregateId,
        private array $payload,
        private array $metadata = []
    ) {
    }

    /**
     * @param array{
     *     eventId: string,
     *     aggregateId: string,
     *     createdAt: string,
     *     metadata: array<string, scalar|array|null>,
     *     payload: array<string, scalar|array|null>,
     *     version: int
     * } $data
     *
     * @return static
     *
     * @throws AssertionFailed
     *
     * @psalm-pure
     */
    public static function fromArray(array $data): Event
    {
        Assert::keyExists($data, 'eventId');
        Assert::keyExists($data, 'aggregateId');
        Assert::keyExists($data, 'payload');
        Assert::keyExists($data, 'metadata');
        Assert::keyExists($data, 'createdAt');
        Assert::keyExists($data, 'version');

        $event = new static(AggregateRootId::fromString($data['aggregateId']), $data['payload'], $data['metadata']);
        $event->id = EventId::fromString($data['eventId']);
        /** @psalm-suppress PossiblyFalsePropertyAssignmentValue */
        $event->createdAt = \DateTimeImmutable::createFromFormat(self::CREATED_FORMAT, $data['createdAt']);
        $event->version = $data['version'];

        return $event;
    }

    /**
     * @param array<string, scalar|array|null> $payload
     * @param array<string, scalar|array|null> $metadata
     *
     * @return static
     *
     * @psalm-pure
     */
    public static function occur(AggregateRootId $aggregateId, array $payload, array $metadata = []): Event
    {
        $event = new static($aggregateId, $payload, $metadata);
        $event->id = EventId::generate();
        $event->version = 1;
        /** @noinspection PhpUnhandledExceptionInspection */
        $event->createdAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        return $event;
    }

    public function getAggregateId(): AggregateRootId
    {
        return $this->aggregateId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getId(): EventId
    {
        return $this->id;
    }

    /**
     * @return array<string, scalar|array|null>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @return array<string, scalar|array|null>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

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
    public function toArray(): array
    {
        return [
            'eventId' => (string) $this->id,
            'aggregateId' => (string) $this->aggregateId,
            'createdAt' => $this->createdAt->format(self::CREATED_FORMAT),
            'metadata' => $this->metadata,
            'payload' => $this->payload,
            'version' => $this->version,
        ];
    }

    /**
     * @psalm-param scalar|null $value
     */
    public function withMetadata(string $key, mixed $value): static
    {
        $event = clone $this;
        $event->metadata[$key] = $value;

        return $event;
    }

    public function withVersion(int $version): static
    {
        $event = clone $this;
        $event->version = $version;

        return $event;
    }
}
