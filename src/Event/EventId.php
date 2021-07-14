<?php
declare(strict_types=1);

namespace MyOnlineStore\EventSourcing\Event;

use MyOnlineStore\EventSourcing\Exception\AssertionFailed;
use MyOnlineStore\EventSourcing\Service\Assert;
use Ramsey\Uuid\Uuid;

/**
 * @psalm-immutable
 */
final class EventId
{
    private function __construct(
        private string $id
    ) {
    }

    /**
     * @throws AssertionFailed
     *
     * @psalm-pure
     */
    public static function fromString(string $id): self
    {
        Assert::uuid($id);

        return new self($id);
    }

    /**
     * @psalm-pure
     */
    public static function generate(): self
    {
        /**
         * @noinspection PhpUnhandledExceptionInspection
         * @psalm-suppress ImpureMethodCall
         */
        return new self(Uuid::uuid4()->toString());
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
