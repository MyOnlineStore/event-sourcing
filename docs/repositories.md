# Repositories

## AggregateRepository

The `AggregateRepository` is responsible for loading and saving aggregates. The interface specifies two methods:
`save(AggregateRoot $aggregateRoot): void` and `load(AggregateRootId $aggregateRootId): AggregateRoot`. It is
implemented in `EventAggregateRepository`, which uses an `EventRepository` to store and retrieve its events. When
storing an aggregate, its recorded events are pulled via `AggregateRoot::popRecordedEvents` and passed to the event
repository, which appends the events to the storage. Note that the `load` method returns an aggregate without history
if requesting a non-existing aggregate when using the default `AggregateFactory`.

A runtime caching decorator is available via `RuntimeCachedAggregateRepository`, which could be used to add runtime
cache to the `AggregateRepository`.


## EventRepository

The `EventRepository` is responsible for loading and storing event streams. The interface specifies two methods:
`appendTo(string $streamName, AggregateRootId $aggregateRootId, Stream $eventStream): void` which appends new events to
the storage, and `public function load(string $streamName, AggregateRootId $aggregateRootId, StreamMetadata $metadata): Stream`
which loads all events for given stream name from storage. A default implementation that uses a doctrine DBAL connection
is available via `DBALEventRepository`. This implementation converts the event payload and metadata to JSON before
persisting to storage.

An `EncryptionKeyGeneratingEventRepository` decorator is available when using `FieldEncrypting` events. This decorator
will automatically generate and save a key to encrypt the specified event payload.

A `DispatchingEventRepository` decorator is available, which uses a `Psr\EventDispatcher\EventDispatcherInterface` to
dispatch events to any available listener that is not part of the aggregate.

`SymfonyMessengerEventRepository` is similar to `DispatchingEventRepository`, but uses
`Symfony\Component\Messenger\MessageBusInterface` to dispatch the events to a message bus.


## MetadataRepository

The `MetadataRepository` stores and loads metadata for a given event stream. An `EmptyMetadataRepository` implementation
is available when stream metadata is not needed. It provides an empty metadata set. A doctrine DBAL implementation is
available via `DBALMetadataRepository`, which uses a DBAL connection to store and retrieve metadata. Metadata stored
by this implementation is converted to JSON.
