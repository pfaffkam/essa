<?php

namespace PfaffKIT\Essa\Shared;

/**
 * Identity interface.
 */
interface Identity
{
    /**
     * Generate new identity.
     */
    public static function new(): Identity;

    /**
     * Create identity object from existing string.
     */
    public static function fromString(string $id): Identity;

    /**
     * Create identity object from existing binary.
     */
    public static function fromBinary(string $id): Identity;

    /**
     * Convert identity to binary.
     */
    public function toBinary(): string;

    /**
     * Check equality of identities.
     */
    public function equals(Identity $identity): bool;

    /**
     * Convert identity to string.
     */
    public function __toString(): string;
}
