<?php

namespace PfaffKIT\Essa\EventSourcing\Bus;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\EventSourcing\EventBus;
use PfaffKIT\Essa\EventSourcing\Exception\EventHandlerException;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class InMemorySymfonyEventBus implements EventBus
{
    public function __construct(
        #[Target('essa.bus.event')]
        private MessageBusInterface $bus,
    ) {}

    /**
     * @throws ExceptionInterface|EventHandlerException
     */
    public function dispatch(AggregateEvent ...$events): void
    {
        $this->dispatchStamped([], ...$events);
    }

    /**
     * @throws EventHandlerException|ExceptionInterface
     */
    public function dispatchStamped(array $stamps, AggregateEvent ...$events): void
    {
        try {
            foreach ($events as $event) {
                $this->bus->dispatch($event, $stamps);
            }
        } catch (HandlerFailedException $error) {
            throw new EventHandlerException($error->getPrevious() ?? $error);
        }
    }
}
