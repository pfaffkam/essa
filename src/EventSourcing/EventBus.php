<?php

namespace PfaffKIT\Essa\EventSourcing;

/** Message bus for AggregateEvents */
interface EventBus
{
    public function dispatch(AggregateEvent ...$events): void;
}
