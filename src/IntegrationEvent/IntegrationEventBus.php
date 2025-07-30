<?php

namespace PfaffKIT\Essa\IntegrationEvent;

interface IntegrationEventBus
{
    public function dispatch(IntegrationEvent ...$events): void;

    public function dispatchStamped(array $stamps, IntegrationEvent ...$events): void;
}
