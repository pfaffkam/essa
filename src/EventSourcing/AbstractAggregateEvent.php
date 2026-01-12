<?php

namespace PfaffKIT\Essa\EventSourcing;

use PfaffKIT\Essa\Shared\EventTimestamp;
use PfaffKIT\Essa\Shared\Id;
use PfaffKIT\Essa\Shared\Identity;

abstract readonly class AbstractAggregateEvent implements AggregateEvent
{
    public Identity $eventId;
    public Identity $aggregateId; // This is set 'magically' by the ESAggregateRoot - to allow clean constructor property promotion.
    public EventTimestamp $timestamp;

    public function __construct(?Identity $eventId = null, ?EventTimestamp $timestamp = null, ?Identity $aggregateId = null)
    {
        $this->eventId = $eventId ?? Id::new();
        $this->timestamp = $timestamp ?? EventTimestamp::now();

        if (null !== $aggregateId) {
            $this->aggregateId = $aggregateId;
        }
    }

    abstract public static function getEventName(): string;
}
