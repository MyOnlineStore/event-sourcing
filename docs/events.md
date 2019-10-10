# Events

Events are immutable objects that must implement the `MyOnlineStore\EventSourcing\Event\Event` interface. A basic
implementation is provided with `MyOnlineStore\EventSourcing\Event\BaseEvent` that can be extended.

Every event must have a unique `EventId`. An event must also contain an `AggregateRootId` for which the event occurred.
Events also have a payload, which is an array that must only contain scalar values or sub-arrays that contain scalar
values. If no payload is required for an event, an empty array could be passed as payload. Events may also have metadata,
which is also an array of scalars or sub-arrays with scalars. Metadata could be, for example, a client IP, timezone, etc
if such information is of value for an event. Finally, events contain a timestamp and a version. The timestamp should be
initialized upon construction of the event. The version is an incremental integer that is managed by the `AggregateRoot`,
it is increased for every subsequent event that occurs in a given `AggregateRoot`. It prevents concurrency issues and
allows rebuilding current state from an out-of-date snapshot.

The `BaseEvent` implementation does not allow construction via `__construct`, but must be constructed via its named
constructor `occur`. For example:
```php
$event = BaseEvent::occur(
    $aggregateRootId,
    ['foo' => 'bar'], // payload
    ['ip' => '127.0.0.1'] // metadata
);
```

On construction, a timestamp for the current `UTC` time will be created. The version will be `1` by default and should
be managed by the `AggregateRoot`. A unique `EventId` will be generated as well. It is recommended to not use the
`BaseEvent` directly, but to extend it and name an event correctly to its intent.


## Stream

A `MyOnlineStore\EventSourcing\Event\Stream` is a collection of events with optional metadata for given stream attached.


## StreamMetadata

`StreamMetadata` contains parameters that are related to a particular event stream. This could for example be an
encryption key for [`FieldEncrypting`](#fieldencrypting) events. This metadata is passed to the [`EventConverter`](#converter)s
, which may need it for conversion.


## Serialization

To be able to store events in storage, they should be serializable. An `ArraySerializable` interface is provided that
describes serialization to and from arrays. It may be implemented in your own `Event` implementation and is available in
the `BaseEvent` implementation. The interface contains two methods: `toArray(): array` and `fromArray(array $data): Event`.
The data passed to and returned from these methods should contain the following keys: `event_id`, `aggregate_id`,
`created_at`, `metadata`, `payload`, `version`. Values should all be scalars or arrays of scalars in case of `payload`.


## Converter

An `EventConverter` converts an event to and from an array. This converter may use the methods provided by
`ArraySerializable` if implemented. An `ArraySerializableEventConverter` is provided that can convert events that
implement `ArraySerializable`. `StreamMetadata` is a required parameter because its contents may be of importance
during conversion as is the case for [`FieldEncrypting`](#fieldencrypting) events. A `FieldEncryptingConverter` is
provided as decorator for your default `EventConverter`, which is able to encrypt and decrypt specific fields of an
event payload. If an event does not implement the `FieldEncrypting` interface, the `FieldEncryptingConverter` simply
passes the data to the decorated converter and does nothing.


## FieldEncrypting

The `FieldEncrypting` event interface allows specific payload fields of an event to be encrypted before persisting to
storage and decrypting them after reading from storage. This allows for GDPR compliance when an immutable event storage
is used. The interface specifies one method: `getEncryptingFields`, which should return the names of the payload fields
that should be en-/decrypted. The encryption key is stored in `StreamMetadata`.
