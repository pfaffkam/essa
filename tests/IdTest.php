<?php

namespace PfaffKIT\Essa\Tests;

use PfaffKIT\Essa\Shared\Id;
use PfaffKIT\Essa\Shared\Identity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Id::class)]
class IdTest extends TestCase
{
    public function testNew(): void
    {
        $id = Id::new();
        self::assertInstanceOf(Id::class, $id);
        self::assertInstanceOf(Identity::class, $id);
    }

    public function testCreateIdFromString(): void
    {
        $uuidString = '123e4567-e89b-12d3-a456-426614174000';
        $identity = Id::fromString($uuidString);
        $this->assertInstanceOf(Identity::class, $identity);
        $this->assertEquals($uuidString, (string) $identity);
    }

    public function testCreateIdFromBinary(): void
    {
        $uuidBinary = hex2bin('123e4567e89b12d3a456426614174000');
        $identity = Id::fromBinary($uuidBinary);
        $this->assertInstanceOf(Identity::class, $identity);
        $this->assertEquals($uuidBinary, $identity->toBinary());
    }

    public function testEqualsReturnsTrueForSameIdentity(): void
    {
        $uuidString = '123e4567-e89b-12d3-a456-426614174000';
        $identity1 = Id::fromString($uuidString);
        $identity2 = Id::fromString($uuidString);
        $this->assertTrue($identity1->equals($identity2));
    }

    public function testEqualsReturnsFalseForDifferentIdentity(): void
    {
        $identity1 = Id::new();
        $identity2 = Id::new();
        $this->assertFalse($identity1->equals($identity2));
    }

    public function testToString(): void
    {
        $uuidString = '123e4567-e89b-12d3-a456-426614174000';
        $identity = Id::fromString($uuidString);
        $this->assertEquals($uuidString, (string) $identity);
    }
}
