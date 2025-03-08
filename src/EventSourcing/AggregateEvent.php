<?php

namespace PfaffKIT\Essa\EventSourcing;

use PfaffKIT\Essa\Shared\Identity;

/**
 * Base class for Aggregate Events.
 * Aggregate Events are immutable objects that represent a change in the state of an Aggregate.
 */
interface AggregateEvent
{
    public Identity $aggregateId { get; }
    public Identity $eventId { get; }
    public \DateTimeImmutable $timestamp { get; }

    public static function getEventName(): string;
}
