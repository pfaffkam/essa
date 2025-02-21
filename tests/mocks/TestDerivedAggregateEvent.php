<?php

namespace PfaffKIT\Essa\Tests\mocks;

use PfaffKIT\Essa\EventSourcing\AbstractAggregateEvent;

readonly class TestDerivedAggregateEvent extends AbstractAggregateEvent
{
    public function __construct(
        public string $stringData,
    ) {
        parent::__construct();
    }

    public static function getEventName(): string
    {
        return 'test_derived_event';
    }
}
