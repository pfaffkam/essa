<?php

namespace PfaffKIT\Essa\EventSourcing\Exception;

class ProjectionHandlerException extends \Exception implements EssaException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(
            message: 'An error occurred while handling event.',
            previous: $previous
        );
    }
}
