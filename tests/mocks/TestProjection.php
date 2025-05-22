<?php

namespace PfaffKIT\Essa\Tests\mocks;

use PfaffKIT\Essa\EventSourcing\Projection;
use PfaffKIT\Essa\Shared\Identity;

class TestProjection implements Projection
{
    public function __construct(
        public Identity $id,
        public string $name,
        public int $value,
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
}
