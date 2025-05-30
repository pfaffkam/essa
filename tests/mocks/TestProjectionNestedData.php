<?php

namespace PfaffKIT\Essa\Tests\mocks;

class TestProjectionNestedData
{
    public function __construct(
        public string $nestedName,
        public int $nestedValue,
        public ?\DateTimeImmutable $nestedDate = null,
    ) {
        $this->nestedDate = $nestedDate ?? new \DateTimeImmutable();
    }
}
