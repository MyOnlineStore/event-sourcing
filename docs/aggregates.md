# Aggregate

Aggregates must extend `MyOnlineStore\EventSourcing\Aggregate\AggregateRoot`. Every aggregate root has a unique
`AggregateRootId`. It is recommended to extend the `AggregateRootId` for every aggregate in your domain to allow strict
typing of the IDs. An aggregate is versioned, this means that every change of state will increase its version. This
allows for preventing concurrency issues. Moreover, it allows us to load a certain state from a snapshot and apply all
newer events to the aggregate to get to the most recent state.


## Creating & Handling events in an aggregate

The example below demonstrates how an event is created within an aggregate and how it is handled. Important is that
within the creation method (`changeName` in the example), no state changed occur. State of the aggregate is only changed
in event handlers. An event handler should be named after the event, eg `apply[EventName]`. The event handlers are
called when loading an aggregate from storage (see [aggregate factory](#aggregate-factory)), the creation methods are
not.

```php
use MyOnlineStore\EventSourcing\Aggregate\AggregateRoot;
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Event\BaseEvent;

final class NameChanged extends BaseEvent
{
    public static function byCustomer(AggregateRootId $customerId, string $name): self
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
    /** @var string */
    private $name;

    public function changeName(string $name): void
    {
        $this->recordThat(NameChanged::byCustomer($this->aggregateRootId, $name));
    }

    protected function applyNameChanged(NameChanged $event): void
    {
        $this->name = $event->getName();
    }
}
```


## Aggregate Factory

Aggregates can be constructed with `MyOnlineStore\EventSourcing\Aggregate\AggregateFactory`. A default implementation is
provided that constructs an aggregate via its FQCN in `FQCNAggregateFactory`. It will call `reconstituteFromHistory` on
the aggregate, rebuilding its state from events by calling their handlers. NOTE: it allows construction of aggregates
without history.
