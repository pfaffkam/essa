<?php

namespace PfaffKIT\Essa\Test;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\EventSourcing\ESAggregateRoot;
use PfaffKIT\Essa\EventSourcing\Storage\ESAggregateRepository;
use PfaffKIT\Essa\Shared\Identity;

/**
 * Simple in-memory repository to test ES aggregates.
 */
class InMemoryESAggregateRepository implements ESAggregateRepository
{
    private array $data = [];

    /** @param class-string<ESAggregateRoot> $type */
    public function __construct(private string $type) {}

    public function persist(ESAggregateRoot $root): void
    {
        $eventExtractor = fn () => $this->popRecordedEvents();

        /** @var AggregateEvent[] $events */
        $events = $eventExtractor->call($root);

        $id = (string) $root->id;
        isset($this->data[$id]) ? array_push($this->data[$id], $events) : $this->data[$id] = $events;
    }

    public function getById(Identity $id): ?ESAggregateRoot
    {
        $events = $this->data[(string) $id];

        if (!$events) {
            return null;
        }

        return $this->type::fromEventStream($id, $events);
    }
}
