<?php

namespace PfaffKIT\Essa\EventSourcing\Storage;

use PfaffKIT\Essa\EventSourcing\ESAggregateRoot;
use PfaffKIT\Essa\Shared\Identity;

/**
 * @template T of ESAggregateRoot
 */
interface ESAggregateRepository
{
    /** @param T $root */
    public function persist(ESAggregateRoot $root): void;

    /** @return T|null */
    public function getById(Identity $id): ?ESAggregateRoot;
}
