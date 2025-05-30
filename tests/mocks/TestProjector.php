<?php

namespace PfaffKIT\Essa\Tests\mocks;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\EventSourcing\Projection\Projection;
use PfaffKIT\Essa\EventSourcing\Projection\Projector;

class TestProjector implements Projector
{
    public function load(AggregateEvent $event): ?Projection
    {
        return null;
    }

    public function save(Projection $projection): void {}
}
