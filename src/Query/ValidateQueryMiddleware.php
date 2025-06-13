<?php

namespace PfaffKIT\Essa\Query;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class ValidateQueryMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if (!$message instanceof Query) {
            throw new \InvalidArgumentException(sprintf('Message of type "%s" is not allowed on this bus. It must implement %s', get_class($message), Query::class));
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
