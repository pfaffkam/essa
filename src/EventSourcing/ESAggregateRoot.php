<?php

namespace PfaffKIT\Essa\EventSourcing;

use PfaffKIT\Essa\Shared\Identity;

/**
 * Base class for **event-sourced** aggregate roots.
 *
 * It is responsible for tracking changes of aggregate and
 * reconstructing the aggregate from events.
 */
abstract class ESAggregateRoot
{
    /**
     * List of events that are not yet commited to the event store.
     *
     * @var AggregateEvent[]
     */
    private array $recordedEvents = [];

    public function __construct(
        protected(set) Identity $id,
    ) {}

    /**
     * Instantiate aggregate from event stream.
     * Useful when you want to reconstruct the aggregate from store.
     */
    public static function fromEventStream(Identity $id, array $events): static
    {
        $instance = new static($id);
        $instance->replay($events);

        return $instance;
    }

    /**
     * Remove all recorded events and return them.
     * This method is used by the event store, executed in 'tricky' way using callback.
     *
     * @return AggregateEvent[]
     */
    protected function popRecordedEvents(): array
    {
        $recordedEvents = $this->recordedEvents;

        $this->recordedEvents = [];

        return $recordedEvents;
    }

    /**
     * Indicate that some event has happened.
     * Should be called only by the aggregate itself.
     */
    protected function recordThat(AggregateEvent $event): void
    {
        $this->recordedEvents[] = $event;

        // nasty way to set aggregate id on event
        $aggregateIdSetter = fn ($id) => $this->aggregateId = $id;
        $aggregateIdSetter->call($event, $this->id);

        $this->apply($event);
    }

    /**
     * Recreate aggregate from past event stream.
     *
     * @param AggregateEvent[] $events
     */
    protected function replay(array $events): void
    {
        foreach ($events as $event) {
            $this->apply($event);
        }
    }

    abstract protected function apply(AggregateEvent $event): void;
}
