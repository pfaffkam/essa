<?php

namespace PfaffKIT\Essa\EventSourcing\Storage;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\EventSourcing\ESAggregateRoot;
use PfaffKIT\Essa\EventSourcing\EventBus;
use PfaffKIT\Essa\Shared\Identity;

abstract class AbstractAggregateRepository implements AggregateRepository
{
    public function __construct(
        private readonly EventStorage $storage,
        private readonly EventBus $eventBus,
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
        $events = $this->storage->load($id);

        if (empty($events)) {
            return null;
        }

        return $this->instantiateAggregate($id, $events);
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

    abstract public static function getType(): string;
}
