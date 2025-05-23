<?php

namespace PfaffKIT\Essa\EventSourcing\Projection;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class HandlerFilterMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $stamp = $envelope->last(HandlerFilterStamp::class);

        if (!$stamp) {
            return $stack->next()->handle($envelope, $stack);
        }

        $handlers = $envelope->all(HandledStamp::class);

        if (empty($handlers)) {
            return $stack->next()->handle($envelope, $stack);
        }

        $filteredHandlers = [];
        foreach ($handlers as $handler) {
            if (!$handler instanceof HandledStamp) {
                continue;
            }

            $handlerClass = $this->getHandlerClass($handler);
            if (in_array($handlerClass, $stamp->allowedHandlerClasses, true)) {
                $filteredHandlers[] = $handler;
            }
        }

        if (empty($filteredHandlers)) {
            return $envelope;
        }

        $envelope = $envelope->withoutAll(HandledStamp::class);
        foreach ($filteredHandlers as $handler) {
            $envelope = $envelope->with($handler);
        }

        return $stack->next()->handle($envelope, $stack);
    }

    private function getHandlerClass(HandledStamp $handler): string
    {
        $handlerName = $handler->getHandlerName();

        // Extract the class name before the last ':'
        $lastColonPos = strrpos($handlerName, ':');
        if (false === $lastColonPos) {
            throw new \RuntimeException(sprintf('Invalid handler name format: %s', $handlerName));
        }

        return substr($handlerName, 0, $lastColonPos);
    }
}
