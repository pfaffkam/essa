<?php

namespace PfaffKIT\Essa\Tests\mocks;

use PfaffKIT\Essa\EventSourcing\Projection;
use PfaffKIT\Essa\Shared\Identity;

class AnotherTestProjection implements Projection
{
    public function __construct(
        public Identity $id,
    ) {}

    public static function getProjectionName(): string
    {
        return 'another_test_projection';
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
