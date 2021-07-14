# SQL Based Storage

The following schemas are needed when using an SQL based storage such as the default DBAL repository implementations.
The given examples are for PostgreSQL, but these could be adjusted for MySQL or others. `event_stream` should be
replaced by the lowercase name of your aggregate(s).

## EventRepository

```postgresql
CREATE TABLE event_stream (
    event_id UUID PRIMARY KEY,
    event_name VARCHAR(100) NOT NULL,
    aggregate_id UUID NOT NULL,
    payload JSON NOT NULL,
    metadata JSONB NOT NULL,
    created_at TIMESTAMP(6) NOT NULL,
    version INT,
    UNIQUE (aggregate_id, version)
);
```


## MetadataRepository

```postgresql
CREATE TABLE event_stream_metadata (
    aggregate_id UUID PRIMARY KEY,
    metadata JSONB NOT NULL
);
```


## SnapshotRepository
```postgresql
CREATE TABLE event_stream_snapshot (
    aggregate_id UUID PRIMARY KEY,
    version INT,
    state TEXT NOT NULL
);
```
