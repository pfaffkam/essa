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

        return []; // This line will never be reached but satisfies static analysis
    }

    public function loadInBatches(
        int $offset = 0,
        int $batchSize = self::DEFAULT_BATCH_SIZE,
        array $eventTypes = [],
    ): iterable {
        $this->generateException();
        yield from []; // This line will never be reached but satisfies static analysis
    }

    private function generateException(): void
    {
        throw new \LogicException('No implementation provided for EventStorage. Please install one of the proper adapters or create your own.');
    }
}
