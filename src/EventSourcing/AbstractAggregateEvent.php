<?php

namespace PfaffKIT\Essa\EventSourcing;

use PfaffKIT\Essa\Shared\Id;
use PfaffKIT\Essa\Shared\Identity;

abstract readonly class AbstractAggregateEvent implements AggregateEvent
{
    public Identity $eventId;
    public Identity $aggregateId; // This is set 'magically' by the ESAggregateRoot - to allow clean constructor property promotion.
    public \DateTimeImmutable $timestamp;

    public function __construct(?Identity $eventId = null, ?\DateTimeImmutable $timestamp = null, ?Identity $aggregateId = null)
    {
        $this->eventId = $eventId ?? Id::new();
        $this->timestamp = $timestamp ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        if (null !== $aggregateId) {
            $this->aggregateId = $aggregateId;
        }
    }

    abstract public static function getEventName(): string;
}
