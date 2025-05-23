<?php

namespace PfaffKIT\Essa\Tests\mocks;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\Shared\Identity;

class TestEvent implements AggregateEvent
{
    public function __construct(
        public Identity $aggregateId,
        public Identity $eventId,
        public \DateTimeImmutable $timestamp,
    ) {}

    public function getAggregateId(): Identity
    {
        return $this->aggregateId;
    }

    public function getEventId(): Identity
    {
        return $this->eventId;
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }

    public static function getEventName(): string
    {
        return 'test.event';
    }
}
