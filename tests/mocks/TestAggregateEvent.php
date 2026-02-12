<?php

namespace PfaffKIT\Essa\Tests\mocks;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\Shared\EventTimestamp;
use PfaffKIT\Essa\Shared\Identity;

class TestAggregateEvent implements AggregateEvent
{
    public function __construct(
        public Identity $eventId,
        public Identity $aggregateId,
        public EventTimestamp $timestamp,
        public int $actualVersion,
        public string $stringData,
    ) {}

    public static function getEventName(): string
    {
        return 'test_event';
    }

    public static function getVersion(): int
    {
        return 1;
    }
}
