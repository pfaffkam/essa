<?php

namespace PfaffKIT\Essa\EventSourcing\Exception;

class UnsupportedEventException extends \Exception implements EssaException
{
    public function __construct(string $eventName)
    {
        parent::__construct(
            sprintf('Unsupported event: %s', $eventName)
        );
    }
}
