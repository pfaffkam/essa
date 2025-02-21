## Core Design

The core design of ESSA library is based on the two interfaces: the `AggregateEvent` and `ESAggregateRoot`,
which are supported by many other class.


## Flow of data
Basic flow of data is as follows:

1. You request ESAggregateRoot from `AggregateRepository` using some function (e.g. `getById`). 
   It calls proper instance of `EventStorage` to retrieve related events. \
   Events are *deserialized* using `EventSerializer`.
2. You do some work (trigger some ESAggregateRoot methods) which emits some events.
3. You call `persist` on `AggregateRepository` to save all emitted events. It triggers `EventStorage` to save all events. \
   Evenrs are *serialized* using `EventSerialized` and pushed into storage.

## AggregateRepository
AggregateRepository is top level interface which is used directly in your application. 

You should write **your own** implementations of AggregateRepository for each AggregateRoot, by extending `AbstractAggregateRepository` class.

This class is responsible for calling EventStorage of your choice, and building Aggregate from events.

## EventStorage
EventStorage is responsible for storing and retrieving events. It directly contacts to database, **and** prepares data to be stored. \
This data preparation is mostly done by serialization. Implementations of EventStorage should somehow rely on `EventSerializer` interface,
which allows you to customize the serialization process.

@note: All official storage connectors (implementations of EventStorage) are using `EventSerializer` to serialize and deserialize events. \
       Also, be default - it uses `JsonEventSerializer` implementation, which should be enough for most cases, and can be extended if needed.

## EventClassResolver

## EventSerializer

