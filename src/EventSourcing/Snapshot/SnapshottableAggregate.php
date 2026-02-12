<?php

namespace PfaffKIT\Essa\EventSourcing\Snapshot;

use PfaffKIT\Essa\EventSourcing\ESAggregateRoot;
use PfaffKIT\Essa\Shared\Identity;

interface SnapshottableAggregate
{
    /** Create a snapshot array from the aggregate */
    public function toSnapshot(): array;

    /** Reinstantiate aggregate from a snapshot */
    public static function fromSnapshot(Identity $id, array $snapshot): ESAggregateRoot&SnapshottableAggregate;

    /** Table to save snapshots */
    public static function snapshotName(): string;

    /** Version of snapshots (if changed, the full aggregate will be rebuilt from scratch) */
    public static function snapshotVersion(): int;

    public static function snapshotThreshold(): int;
}
