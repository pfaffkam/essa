<?php

namespace PfaffKIT\Essa\EventSourcing\Storage;

use PfaffKIT\Essa\Shared\Identity;

class FallbackEventStorage implements EventStorage
{
    public function save(Identity $aggregateId, array $aggregateEvents): void
    {
        $this->generateException();
    }

    public function load(Identity $aggregateId): array
    {
        $this->generateException();
    }

    private function generateException(): void
    {
        throw new \LogicException('No implementation provided for EventStorage. Please install one of proper adapters or create your own.');
    }
}
