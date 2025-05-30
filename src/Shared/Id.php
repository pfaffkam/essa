<?php

namespace PfaffKIT\Essa\Shared;

use Symfony\Component\Uid\Uuid;

readonly class Id implements Identity
{
    public function __construct(private Uuid $uuid) {}

    public static function new(): Identity
    {
        return new self(Uuid::v7());
    }

    public static function fromString(string $id): Identity
    {
        return new self(Uuid::fromString($id));
    }

    public static function fromBinary(string $id): Identity
    {
        return new self(Uuid::fromBinary($id));
    }

    public function toBinary(): string
    {
        return $this->uuid->toBinary();
    }

    public function equals(Identity $identity): bool
    {
        return $this->uuid->toBinary() === $identity->toBinary();
    }

    public function __toString(): string
    {
        return $this->uuid->toString();
    }
}
