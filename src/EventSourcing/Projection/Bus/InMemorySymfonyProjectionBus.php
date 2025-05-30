<?php

namespace PfaffKIT\Essa\EventSourcing\Projection\Bus;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\EventSourcing\Exception\ProjectionHandlerException;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class InMemorySymfonyProjectionBus implements ProjectionBus
{
    public function __construct(
        #[Target('essa.bus.projection')]
        private MessageBusInterface $bus,
    ) {}

    /**
     * @throws ProjectionHandlerException|ExceptionInterface
     */
    public function dispatch(AggregateEvent ...$events): void
    {
        $this->dispatchStamped([], ...$events);
    }

    /**
     * @throws ProjectionHandlerException|ExceptionInterface
     */
    public function dispatchStamped(array $stamps, AggregateEvent ...$events): void
    {
        try {
            foreach ($events as $event) {
                $this->bus->dispatch($event, $stamps);
            }
        } catch (HandlerFailedException $error) {
            throw new ProjectionHandlerException($error->getPrevious() ?? $error);
        }
    }
}
