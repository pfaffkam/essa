<?php

namespace PfaffKIT\Essa\Tests\mocks;

use PfaffKIT\Essa\EventSourcing\AbstractAggregateEvent;
use PfaffKIT\Essa\Shared\Identity;

readonly class TestDerivedAggregateEvent extends AbstractAggregateEvent
{
    public function __construct(
        Identity $aggregateId,
        public string $stringData,
    ) {
        parent::__construct(aggregateId: $aggregateId);
    }

    public static function getEventName(): string
    {
        return 'test_derived_event';
    }
}
