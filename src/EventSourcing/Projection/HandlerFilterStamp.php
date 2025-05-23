<?php

namespace PfaffKIT\Essa\EventSourcing\Projection;

use Symfony\Component\Messenger\Stamp\StampInterface;

class HandlerFilterStamp implements StampInterface
{
    public array $allowedHandlerClasses {
        get { return $this->allowedHandlerClasses; }
    }

    /**
     * @param class-string<Projector> ...$allowedHandlerClasses
     */
    public function __construct(
        string ...$allowedHandlerClasses,
    ) {
        $this->allowedHandlerClasses = $allowedHandlerClasses;
    }
}
