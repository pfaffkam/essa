<?php

namespace PfaffKIT\Essa\EventSourcing\Projection;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\EventSourcing\Attribute\AsProjector;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;

/**
 * @internal
 */
readonly class ProjectionHandlerLocator implements HandlersLocatorInterface
{
    public function __construct(
        private HandlersLocatorInterface $innerLocator,
        private LoggerInterface $messageLogger,
    ) {}

    public function getHandlers(Envelope $envelope): iterable
    {
        $handlers = $this->innerLocator->getHandlers($envelope);

        foreach ($handlers as $handler) {
            if (!$handler instanceof HandlerDescriptor) {
                yield $handler;
                continue;
            }

            $handlerCallable = $handler->getHandler();

            try {
                $reflectionMethod = new \ReflectionFunction($handlerCallable);
            } catch (\ReflectionException $e) {
                $this->messageLogger->debug('Projector - cannot reflect callable.', ['exception' => $e]);
                yield $handler;
                continue;
            }

            // Should be applied only with AsProjector attributes.
            if (0 == count($reflectionMethod->getAttributes(AsProjector::class))) {
                $this->messageLogger->debug('Projector - used different attribute than AsProjector.');
                yield $handler;
                continue;
            }

            $parameters = $reflectionMethod->getParameters();

            if (1 == count($parameters)) {
                yield $handler;
                continue;
            } elseif (2 == count($parameters)) {
                $paramType = $parameters[1]->getType();

                if (!$paramType->getName() || !is_subclass_of($paramType->getName(), Projection::class)) {
                    throw new ProjectorException('When projector have two parameters, second must be of Projection type.');
                }
            } else {
                throw new ProjectorException('Projector must have 1 parameter (only event) or 2 parameters (event, projection)');
            }

            $projector = $reflectionMethod->getClosureThis();

            yield new HandlerDescriptor(function (AggregateEvent $event) use ($handler, $projector) {
                $callable = $handler->getHandler();

                $projection = $projector->load($event);

                $result = $callable($event, $projection);

                if ($projection instanceof Projection) {
                    $projector->save($projection);
                }

                return $result;
            });
        }
    }
}
