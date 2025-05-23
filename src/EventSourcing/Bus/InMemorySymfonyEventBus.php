<?php

namespace PfaffKIT\Essa\EventSourcing\Bus;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\EventSourcing\EventBus;
use PfaffKIT\Essa\EventSourcing\Exception\ProjectionEventException;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class InMemorySymfonyEventBus implements EventBus
{
    public function __construct(
        #[Target('essa.bus.event')]
        private MessageBusInterface $bus,
    ) {}

    /**
     * @throws ProjectionEventException|ExceptionInterface
     */
    public function dispatch(AggregateEvent ...$events): void
    {
        $this->dispatchStamped([], ...$events);
    }

    /**
     * @throws ProjectionEventException|ExceptionInterface
     */
    public function dispatchStamped(array $stamps, AggregateEvent ...$events): void
    {
        dump('dispatching events');
        try {
            foreach ($events as $event) {
                dump($event::class);
                $this->bus->dispatch($event, $stamps);
            }
        } catch (NoHandlerForMessageException) {
            dd('no handler - this situation should not happen');
        } catch (HandlerFailedException $error) {
            throw new ProjectionEventException($error->getPrevious() ?? $error);
        }
    }
}
