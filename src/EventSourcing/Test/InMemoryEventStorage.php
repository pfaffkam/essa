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

    public function loadInBatches(
        int $offset = 0,
        int $batchSize = self::DEFAULT_BATCH_SIZE,
        array $eventTypes = [],
    ): iterable {
        // Flatten all events from all aggregates
        $allEvents = [];
        foreach ($this->storage as $aggregateEvents) {
            $allEvents = array_merge($allEvents, $aggregateEvents);
        }

        // Filter by event types if specified
        if (!empty($eventTypes)) {
            $allEvents = array_filter(
                $allEvents,
                fn ($event) => in_array(get_class($event), $eventTypes, true)
            );
            // Reindex array after filtering
            $allEvents = array_values($allEvents);
        }

        // Apply offset and limit
        $totalEvents = count($allEvents);
        $position = $offset;

        while ($position < $totalEvents) {
            $batch = array_slice($allEvents, $position, $batchSize);
            if (empty($batch)) {
                break;
            }

            yield $batch;
            $position += count($batch);

            // If we got less events than batch size, we've reached the end
            if (count($batch) < $batchSize) {
                break;
            }
        }
    }
}
