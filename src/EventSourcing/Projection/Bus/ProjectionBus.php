<?php

namespace PfaffKIT\Essa\EventSourcing\Projection\Bus;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;

/** Message bus for creating projections */
interface ProjectionBus
{
    public function dispatch(AggregateEvent ...$events): void;
    public function dispatchStamped(array $stamps, AggregateEvent ...$events): void;
}
