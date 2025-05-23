<?php

namespace PfaffKIT\Essa\Tests\mocks;

use PfaffKIT\Essa\EventSourcing\Projection\Projector;
use PfaffKIT\Essa\Tests\mocks\TestEvent;

class TestProjector implements Projector
{
    public function handleTestEvent(TestEvent $event): void
    {
        // Handle the test event
    }
}
