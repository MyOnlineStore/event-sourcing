<?php
declare(strict_types=1);

/**
 * Quick start example that demonstrates basic usage
 */

use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\BaseEvent;
use MyOnlineStore\EventSourcing\Listener\Attribute\Listener;

final class CustomerId extends AggregateRootId
{
}

final class Registered extends BaseEvent
{
    public static function withName(CustomerId $customerId, string $name): self
    {
        return self::occur($customerId, ['name' => $name]);
    }

    public function getCustomerId(): CustomerId
    {
        return CustomerId::fromString((string) $this->getId());
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
    private CustomerId $id;
    private string $name;
    private DateTimeImmutable $registeredAt;

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

    #[Listener(NameChanged::class)]
    #[Listener(Registered::class)]
    protected function nameChanged(NameChanged|Registered $event): void
    {
        $this->name = $event->getName();
    }

    #[Listener(Registered::class)]
    protected function registered(Registered $event): void
    {
        $this->id = $event->getCustomerId();
        $this->registeredAt = $event->getCreatedAt();
    }
}
