<?php

namespace PfaffKIT\Essa\IntegrationEvent;

use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class RabbitMqIntegrationEventBus implements IntegrationEventBus
{
    public function __construct(
        #[Target('essa.bus.integration_event')]
        private MessageBusInterface $bus,
    ) {}


    public function dispatch(IntegrationEvent ...$events): void
    {
        $this->dispatchStamped([], ...$events);
    }

    public function dispatchStamped(array $stamps, IntegrationEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->bus->dispatch($event, $stamps);
        }
    }
}
