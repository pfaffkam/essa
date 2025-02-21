<?php

namespace PfaffKIT\Essa\Shared;

/**
 * Base class for **non-event-sourced** aggregate roots.
 */
abstract class AggregateRoot
{
    public function __construct(
        protected(set) Identity $id,
    ) {}
}
