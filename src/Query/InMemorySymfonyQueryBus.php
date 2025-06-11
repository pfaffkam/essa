<?php

namespace PfaffKIT\Essa\Query;

use App\Domain\SharedKernel\Infrastructure\Bus\Query\QueryNotRegisteredException;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class InMemorySymfonyQueryBus implements QueryBus
{
    public function __construct(
        #[Target('essa.bus.query')]
        private MessageBusInterface $bus,
    ) {}

    public function ask(Query $query): mixed
    {
        try {
            /** @var HandledStamp $stamp */
            $stamp = $this->bus->dispatch($query)->last(HandledStamp::class);

            return $stamp->getResult();
        } catch (NoHandlerForMessageException) {
            throw new QueryNotRegisteredException($query);
        }
    }
}
