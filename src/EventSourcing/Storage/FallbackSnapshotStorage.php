<?php

namespace PfaffKIT\Essa\EventSourcing\Storage;

use PfaffKIT\Essa\EventSourcing\Snapshot;
use PfaffKIT\Essa\EventSourcing\SnapshotStorage;
use PfaffKIT\Essa\Shared\Identity;

class FallbackSnapshotStorage implements SnapshotStorage
{
    public function load(string $snapshotName, int $snapshotVersion, Identity $aggregateId): ?Snapshot
    {
        $this->generateException();
    }

    public function save(Snapshot $snapshot): void
    {
        $this->generateException();
    }

    private function generateException(): void
    {
        throw new \LogicException('No implementation provided for SnapshotStorage. Please install one of the proper adapters or create your own.');
    }
}
