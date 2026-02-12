<?php

namespace PfaffKIT\Essa\EventSourcing;

use PfaffKIT\Essa\Shared\Identity;

interface SnapshotStorage
{
    public function load(string $snapshotName, int $snapshotVersion, Identity $aggregateId): ?Snapshot;
    public function save(Snapshot $snapshot): void;
}
