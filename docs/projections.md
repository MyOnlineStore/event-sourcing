# Projections

Projections can be used to create and update `ReadModel`s. A read model represents a view state of a (part) of an
aggregate. It allows for the creation of optimized view models that are performant and do not require loading the state
from an event store. It is important to note that no domain logic should be performed on ReadModels.

The requirement for a read model is that it implements the `ReadModel` interface so that it can be used with the
`ReadModelRepository`. It must also be loadable by its related `AggregateRootId`. The `ReadModelRepository` interface
specifies three methods for saving, loading and deleting read models. There is no default implementation available,
because every implementation will be very specific for a given `ReadModel`. The `load` method must throw a
`ReadModelNotFound` exception when a model can not be found.

To create, update and delete read models, a basic `Projector` is available that can be extended. It is an event listener
that can be used together with the `DispatchingEventRepository`. Event handlers can be defined similar to how it is done
in the `AggregateRoot`. Basic usage can be seen in the next example, using the `NameChanged` event as defined in
[Aggregates](aggregates.md):

```php
use MyOnlineStore\EventSourcing\Aggregate\AggregateRootId;
use MyOnlineStore\EventSourcing\Exception\ReadModelNotFound;
use MyOnlineStore\EventSourcing\Listener\Attribute\Listener;
use MyOnlineStore\EventSourcing\Projection\Projector;

final class CustomerInfoReadModel implements ReadModel
{
    /** @var AggregateRootId */
    public $aggregateRootId;

    /** @var string */
    public $name;

    public function __construct(AggregateRootId $aggregateRootId)
    {
        $this->aggregateRootId = $aggregateRootId;
    }
}

final class CustomerInfoProjector extends Projector
{
    #[Listener(NameChanged::class)]
    protected function nameChanged(NameChanged $event)
    {
        try {
            $model = $this->repository->load($event->getAggregateId());
        } catch (ReadModelNotFound $exception) {
            $model = new CustomerInfoReadModel($event->getAggregateId());
        }
        
        $model->name = $event->getName();
        $this->repository->save($model);
    }
}
```
