<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

use MyOnlineStore\EventSourcing\Exception\AssertionFailed;
use MyOnlineStore\EventSourcing\Service\Assert;
use Ramsey\Uuid\Uuid;

class AggregateRootId
{
    final private function __construct(
        private string $id
    ) {
    }

    /**
     * @throws AssertionFailed
     */
    public static function fromString(string $aggregateRootId): static
    {
        Assert::uuid($aggregateRootId);

        return new static($aggregateRootId);
    }

    public static function generate(): static
    {
        /** @noinspection PhpUnhandledExceptionInspection */

        return new static(Uuid::uuid4()->toString());
    }

    public function equals(self $comparator): bool
    {
        return $this->id === $comparator->id;
    }

    public function toString(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
