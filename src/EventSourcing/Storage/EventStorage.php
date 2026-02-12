<?php

namespace PfaffKIT\Essa\EventSourcing\Storage;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\Shared\EventTimestamp;
use PfaffKIT\Essa\Shared\Identity;

interface EventStorage
{
    public const int DEFAULT_BATCH_SIZE = 100;

    /**
     * @param AggregateEvent[] $aggregateEvents
     */
    public function save(Identity $aggregateId, array $aggregateEvents): void;

    /**
     * @return AggregateEvent[]
     */
    public function load(Identity $aggregateId, ?EventTimestamp $timeFilterAfter = null): array;

    /**
     * Loads events in batches with optional type filtering.
     *
     * @param int $offset    The offset to start from
     * @param int $batchSize Maximum number of events to return in one batch
     *
     * @return iterable<AggregateEvent[]>
     */
    public function loadInBatches(
        int $offset = 0,
        int $batchSize = self::DEFAULT_BATCH_SIZE,
        array $limitEventTypes = [],
        array $limitAggregateIds = [],
        ?EventTimestamp $timeFilterAfter = null,
    ): iterable;

    public function count(array $limitEventTypes = [], array $limitAggregateIds = []): int;
}
