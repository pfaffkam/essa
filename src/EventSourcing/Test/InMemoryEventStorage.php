<?php

namespace PfaffKIT\Essa\EventSourcing\Test;

use PfaffKIT\Essa\EventSourcing\Storage\EventStorage;
use PfaffKIT\Essa\Shared\Identity;

class InMemoryEventStorage implements EventStorage
{
    private array $storage = [];

    public function save(Identity $aggregateId, array $aggregateEvents): void
    {
        if (false === isset($this->storage[(string) $aggregateId])) {
            $this->storage[(string) $aggregateId] = [];
        }

        $this->storage[(string) $aggregateId] += $aggregateEvents;
    }

    public function load(Identity $aggregateId): array
    {
        return $this->storage[(string) $aggregateId] ?? [];
    }
}
