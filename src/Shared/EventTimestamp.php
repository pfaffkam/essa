<?php

namespace PfaffKIT\Essa\Shared;

final readonly class EventTimestamp
{
    public function __construct(
        public int $epoch,
    ) {}

    public static function now(): self
    {
        return new self((int) (microtime(true) * 1000000));
    }

    public function toDateTime(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromFormat('U.u', substr((string) $this->epoch, 0, -6).'.'.substr((string) $this->epoch, -6));
    }
}
