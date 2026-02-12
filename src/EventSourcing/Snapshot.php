<?php

namespace PfaffKIT\Essa\EventSourcing;

use PfaffKIT\Essa\Shared\EventTimestamp;
use PfaffKIT\Essa\Shared\Identity;

class Snapshot
{
    public function __construct(
        public Identity $aggregateId,
        public string $name,
        public int $version,

        public EventTimestamp $lastEventTimestamp,
        public Identity $lastEventId,
        public array $data,
    ) {}
}
