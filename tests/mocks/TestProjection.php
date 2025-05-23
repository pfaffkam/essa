<?php

namespace PfaffKIT\Essa\Tests\mocks;

use PfaffKIT\Essa\EventSourcing\Projection\Projection;
use PfaffKIT\Essa\Shared\Identity;

class TestProjection implements Projection
{
    public function __construct(
        public Identity $id,
        public ?string $name = null,
        public ?int $value = null,
        ?\DateTimeImmutable $updatedAt = null,
        public array $tags = [],
        public ?TestProjectionNestedData $nestedData = null,
    ) {
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
    }

    public ?\DateTimeImmutable $updatedAt = null;

    public static function getProjectionName(): string
    {
        return 'test_projection';
    }

    public static function getProjectorClass(): string
    {
        return TestProjector::class;
    }

    public static function getRepositoryClass(): string
    {
        return TestProjectionRepository::class;
    }
}
