<?php

namespace PfaffKIT\Essa\EventSourcing;

use PfaffKIT\Essa\EventSourcing\Exception\UnsupportedEventException;

class EventClassResolver
{
    private array $eventMap = [];

    public function __construct(
        array $eventClasses,
    ) {
        $this->buildEventMap($eventClasses);
    }

    /** @param class-string<AggregateEvent>[] $eventClasses > */
    private function buildEventMap(array $eventClasses): void
    {
        foreach ($eventClasses as $eventType) {
            $this->eventMap[$eventType::getEventName()] = $eventType;
        }
    }

    public function resolve(string $eventName): string
    {
        return $this->eventMap[$eventName] ?? throw new UnsupportedEventException($eventName);
    }
}
