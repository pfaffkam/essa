<?php

namespace PfaffKIT\Essa\EventSourcing\Storage;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\EventSourcing\ESAggregateRoot;
use PfaffKIT\Essa\EventSourcing\EventBus;
use PfaffKIT\Essa\EventSourcing\Snapshot;
use PfaffKIT\Essa\EventSourcing\Snapshot\SnapshottableAggregate;
use PfaffKIT\Essa\EventSourcing\SnapshotStorage;
use PfaffKIT\Essa\Shared\Identity;
use Psr\Log\LoggerInterface;

abstract class AbstractESAggregateRepository implements ESAggregateRepository
{
    public function __construct(
        private readonly EventStorage $storage,
        private readonly SnapshotStorage $snapshotStorage,
        private readonly EventBus $eventBus,
        private LoggerInterface $essaLogger,
    ) {}

    public function persist(ESAggregateRoot $root): void
    {
        $eventExtractor = fn () => $this->popRecordedEvents();

        /** @var AggregateEvent[] $events */
        $events = $eventExtractor->call($root);

        $this->storage->save($root->id, $events);
        $this->eventBus->dispatch(...$events);
    }

    public function getById(Identity $id): ?ESAggregateRoot
    {
        /** @var class-string<ESAggregateRoot> $class */
        $class = static::getType();

        if ($snapshot = $this->tryToGetFromSnapshot($id)) {
            return $snapshot;
        }

        // fully rebuild aggregate
        $events = $this->storage->load($id);

        if (empty($events)) {
            return null;
        }

        $aggregate = $this->instantiateAggregate($id, $events);

        if (is_subclass_of($class, SnapshottableAggregate::class)) {
            $this->snapshotAggregate($aggregate, $events);
        }

        return $aggregate;
    }

    /**
     * @param AggregateEvent[] $events
     */
    protected function instantiateAggregate(Identity $id, array $events): ESAggregateRoot
    {
        /** @var class-string<ESAggregateRoot> $class */
        $class = static::getType();

        return $class::fromEventStream($id, $events);
    }

    protected function tryToGetFromSnapshot(Identity $id): ?ESAggregateRoot
    {
        /** @var class-string<ESAggregateRoot> $class */
        $class = static::getType();

        if (!is_subclass_of($class, SnapshottableAggregate::class)) {
            return null;
        }

        /** @var class-string<SnapshottableAggregate> $class */
        $snapshot = $this->snapshotStorage->load($class::snapshotName(), $class::snapshotVersion(), $id);

        if (!$snapshot) {
            return null;
        }

        // try to load only newer events
        $events = $this->storage->load($id, $snapshot->lastEventTimestamp);

        if ($events[0]->eventId->toBinary() !== $snapshot->lastEventId->toBinary()) {
            $this->essaLogger->info('Snapshot event ID mismatch. This can be caused by concurrent writes at the same time. Rebuilding aggregate from scratch.', [
                'aggregateId' => (string) $id,
                'snapshotLastEventId' => (string) $snapshot->lastEventId,
                'aggregateLastEventId' => (string) $events[0]->eventId,
                'timestampLimiter' => $snapshot->lastEventTimestamp,
            ]);
            return null;
        }

        // remove first element from array (this event IS in snapshot)
        array_shift($events);

        $aggregate = $class::fromSnapshot($id, $snapshot->data);

        // load all remaining events
        $eventLoader = fn () => $this->replay($events);

        $eventLoader->call($aggregate); // check if this will work

        // Snapshot aggregate if needed
        $this->snapshotAggregate($aggregate, $events);

        return $aggregate;
    }

    /**
     * @param AggregateEvent[] $events
     */
    protected function snapshotAggregate(ESAggregateRoot&SnapshottableAggregate $aggregate, array $events): void
    {
        if (count($events) < $aggregate::snapshotThreshold()) {
            return;
        }

        $lastEvent = $events[array_key_last($events)];

        $snapshot = new Snapshot(
            $aggregate->id,
            $aggregate::snapshotName(),
            $aggregate::snapshotVersion(),
            $lastEvent->timestamp,
            $lastEvent->eventId,
            $aggregate->toSnapshot()
        );

        $this->snapshotStorage->save($snapshot);
    }

    abstract public static function getType(): string;
}
