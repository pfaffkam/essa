<?php

namespace PfaffKIT\Essa\EventSourcing\Exception;

class UnsupportedEventException extends \Exception
{
    public function __construct(string $eventName)
    {
        parent::__construct(
            sprintf('Unsupported event: %s', $eventName)
        );
    }
}
