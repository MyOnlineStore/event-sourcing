<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Aggregate;

use MyOnlineStore\EventSourcing\Exception\AssertionFailed;
use MyOnlineStore\EventSourcing\Service\Assert;
use Ramsey\Uuid\Uuid;

class AggregateRootId
{
    private string $id;

    final private function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return static
     *
     * @throws AssertionFailed
     */
    public static function fromString(string $aggregateRootId): AggregateRootId
    {
        Assert::uuid($aggregateRootId);

        return new static($aggregateRootId);
    }

    /**
     * @return static
     */
    public static function generate(): AggregateRootId
    {
        /** @noinspection PhpUnhandledExceptionInspection */

        return new static(Uuid::uuid4()->toString());
    }

    public function equals(AggregateRootId $comparator): bool
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
