<?php

namespace PfaffKIT\Essa\EventSourcing;

use PfaffKIT\Essa\Shared\Id;
use PfaffKIT\Essa\Shared\Identity;

abstract readonly class AbstractAggregateEvent implements AggregateEvent
{
    public Identity $eventId;
    public \DateTimeImmutable $timestamp;

    public function __construct(?Identity $eventId = null, ?string $name = null, ?\DateTimeImmutable $timestamp = null)
    {
        $this->eventId = $eventId ?? Id::new();
        $this->timestamp = $timestamp ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

    }

    abstract public static function getEventName(): string;
}
