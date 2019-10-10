<?php
declare(strict_types=1);

/**
 * Quick start example that shows basic usage
 */

use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\BaseEvent;

final class CustomerId extends AggregateRootId
{
}

final class Registered extends BaseEvent
{
    public static function withName(CustomerId $customerId, string $name): self
    {
        return self::occur($customerId, ['name' => $name]);
    }

    public function getName(): string
    {
        return $this->getPayload()['name'];
    }
}

final class NameChanged extends BaseEvent
{
    public static function byCustomer(CustomerId $customerId, string $name): self
    {
        return self::occur($customerId, ['name' => $name]);
    }

    public function getName(): string
    {
        return $this->getPayload()['name'];
    }
}

final class Customer extends AggregateRoot
{
    /** @var CustomerId */
    private $id;

    /** @var string */
    private $name;

    /** @var DateTimeImmutable */
    private $registeredAt;

    protected function __construct(CustomerId $id)
    {
        $this->id = $id;

        parent::__construct($id);
    }

    public function getId(): CustomerId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRegisteredAt(): DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public static function register(CustomerId $customerId, string $name): self
    {
        $customer = new self($customerId);
        $customer->recordThat(Registered::withName($customerId, $name));

        return $customer;
    }

    public function changeName(string $name): void
    {
        $this->recordThat(NameChanged::byCustomer($this->id, $name));
    }

    protected function applyNameChanged(NameChanged $event): void
    {
        $this->name = $event->getName();
    }

    protected function applyRegistered(Registered $event): void
    {
        $this->name = $event->getName();
        $this->registeredAt = $event->getCreatedAt();
    }
}
