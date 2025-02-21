<?php

namespace PfaffKIT\Essa\EventSourcing\Storage;

use PfaffKIT\Essa\EventSourcing\ESAggregateRoot;
use PfaffKIT\Essa\Shared\Identity;

interface AggregateRepository
{
    public function persist(ESAggregateRoot $root): void;

    public function getById(Identity $id): ESAggregateRoot;

    /** @return class-string */
    public static function getType(): string;
}
