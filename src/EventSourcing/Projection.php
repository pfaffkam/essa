<?php

namespace PfaffKIT\Essa\EventSourcing;

use PfaffKIT\Essa\Shared\Identity;

// Real object with base data, stored in mongodb or another database.
interface Projection
{
    /** Identity of projection - it can be aggregate id in simple cases. */
    public Identity $id { get; }

    /** String name of storage to allow refactoring  */
    public static function getProjectionName(): string;

    //    /** Resolve event */
    //    protected function apply(AggregateEvent $event): void;
}
