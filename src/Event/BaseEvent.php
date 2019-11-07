<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Exception\AssertionFailed;
use MyOnlineStore\EventSourcing\Service\Assertion;

class BaseEvent implements ArraySerializable
{
    private const CREATED_FORMAT = 'Y-m-d H:i:s.u';

    /** @var EventId */
    private $id;

    /** @var AggregateRootId */
    private $aggregateId;

    /** @var \DateTimeImmutable */
    private $createdAt;

    /** @var mixed[] */
    private $metadata;

    /** @var mixed[] */
    private $payload;

    /** @var int */
    private $version;

    /**
     * @param mixed[] $payload
     * @param mixed[] $metadata
     */
    private function __construct(AggregateRootId $aggregateId, array $payload, array $metadata = [])
    {
        $this->aggregateId = $aggregateId;
        $this->payload = $payload;
        $this->metadata = $metadata;
    }

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
     *
     * @return static
     *
     * @throws AssertionFailed
     */
    public static function fromArray(array $data): Event
    {
        Assertion::keyExists($data, 'event_id');
        Assertion::keyExists($data, 'aggregate_id');
        Assertion::keyExists($data, 'payload');
        Assertion::keyExists($data, 'metadata');
        Assertion::keyExists($data, 'created_at');
        Assertion::keyExists($data, 'version');

        $event = new static(AggregateRootId::fromString($data['aggregate_id']), $data['payload'], $data['metadata']);
        $event->id = EventId::fromString($data['event_id']);
        $event->createdAt = \DateTimeImmutable::createFromFormat(self::CREATED_FORMAT, $data['created_at']);
        $event->version = (int) $data['version'];

        return $event;
    }

    /**
     * @param mixed[] $payload
     * @param mixed[] $metadata
     *
     * @return static
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
     * @return mixed[]
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @return mixed[]
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
     * @return mixed[]
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
    public function toArray(): array
    {
        return [
            'event_id' => (string) $this->id,
            'aggregate_id' => (string) $this->aggregateId,
            'created_at' => $this->createdAt->format(self::CREATED_FORMAT),
            'metadata' => $this->metadata,
            'payload' => $this->payload,
            'version' => $this->version,
        ];
    }

    /**
     * @param mixed $value
     *
     * @return static
     */
    public function withMetadata(string $key, $value): Event
    {
        $event = clone $this;
        $event->metadata[$key] = $value;

        return $event;
    }

    public function withVersion(int $version): Event
    {
        $event = clone $this;
        $event->version = $version;

        return $event;
    }
}
