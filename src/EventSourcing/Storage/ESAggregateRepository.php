<?php

namespace PfaffKIT\Essa\EventSourcing\Storage;

use PfaffKIT\Essa\EventSourcing\ESAggregateRoot;
use PfaffKIT\Essa\Shared\Identity;

interface ESAggregateRepository
{
    public function persist(ESAggregateRoot $root): void;

    public function getById(Identity $id): ?ESAggregateRoot;
}
