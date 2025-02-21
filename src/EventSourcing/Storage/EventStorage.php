<?php

namespace PfaffKIT\Essa\EventSourcing\Storage;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\Shared\Identity;

interface EventStorage
{
    /**
     * @param AggregateEvent[] $aggregateEvents
     */
    public function save(Identity $aggregateId, array $aggregateEvents): void;

    /**
     * @return AggregateEvent[]
     */
    public function load(Identity $aggregateId): array;
}
