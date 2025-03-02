<?php

namespace PfaffKIT\Essa\Tests\mocks;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\Shared\Identity;

class TestAggregateEvent implements AggregateEvent
{
    public function __construct(
        public Identity $aggregateId,
        public Identity $eventId,
        public \DateTimeImmutable $timestamp,

        public string $stringData,
    ) {}

    public static function getEventName(): string
    {
        return 'test_event';
    }
}
