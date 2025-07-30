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
        private LoggerInterface $essaLogger,
    ) {}

    public function getHandlers(Envelope $envelope): iterable
    {
        $handlers = $this->innerLocator->getHandlers($envelope);

        foreach ($handlers as $handler) {
            if (!$handler instanceof HandlerDescriptor) {
                $this->essaLogger->debug('Projector - HandlerLocator got handler that is not HandlerDescriptor.');
                continue;
            }

            $handlerCallable = $handler->getHandler();

            try {
                $reflectionMethod = new \ReflectionFunction($handlerCallable);
            } catch (\ReflectionException $e) {
                $this->essaLogger->error('Projector - cannot reflect callable.', ['exception' => $e]);
                continue;
            }

            // Should be applied only with AsProjector attributes.
            if (0 == count($reflectionMethod->getAttributes(AsProjector::class))) {
                $this->essaLogger->error('Projector - used different attribute than AsProjector.');
                continue;
            }

            if (!$this->isHandlerAllowed($envelope, $handler)) {
                $this->essaLogger->debug('Projector - handler filtered out - '.$this->getHandlerClass($handler));
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

    private function isHandlerAllowed(Envelope $envelope, HandlerDescriptor $handler): bool
    {
        $stamp = $envelope->last(HandlerFilterStamp::class);

        if (!$stamp) {
            return true;
        }

        return in_array($this->getHandlerClass($handler), $stamp->allowedHandlerClasses);
    }

    private function getHandlerClass(HandlerDescriptor $handler): string
    {
        return preg_match('/^(.*):.*$/', $handler->getName(), $matches)
            ? rtrim($matches[1], ':')
            : throw new \RuntimeException(sprintf('Invalid handler name format: %s', $handler->getName()));
    }
}
