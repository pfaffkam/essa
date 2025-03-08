<?php

namespace PfaffKIT\Essa\EventSourcing\Exception;

class ProjectionEventException extends \Exception
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(
            message: 'An error occurred while projecting event.',
            previous: $previous
        );
    }
}
