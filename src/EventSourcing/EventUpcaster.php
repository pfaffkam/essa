<?php

namespace PfaffKIT\Essa\EventSourcing;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Upgrade event from one to another version, based on the SERIALIZED version.
 *
 * @phpstan-type RawEvent array{
 *      _aggregateId: string,
 *      _id: string,
 *      _name: string,
 *      _version: int,
 *      _payload: array<string, mixed>
 *  }
 */
interface EventUpcaster
{
    public function supports(string $eventName, int $version): bool;

    /**
     * @param RawEvent $eventData
     */
    public function upcast(
        array $eventData): array;
}
