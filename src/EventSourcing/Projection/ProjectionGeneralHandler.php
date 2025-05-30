<?php

namespace PfaffKIT\Essa\EventSourcing\Projection;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\EventSourcing\Projection\Bus\ProjectionBus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * General handler for projection events.
 * It retrieves all events from 'event' bus and sends them also to 'projection' bus.
 *
 * This allows changing transport here, and make projections async where other events still can be processed synchronously.
 */
#[AsMessageHandler(bus: 'essa.bus.event')]
readonly class ProjectionGeneralHandler
{
    public function __construct(
        private ProjectionBus $projectionBus,
    ) {}

    public function __invoke(AggregateEvent $event): void
    {
        $this->projectionBus->dispatchStamped([], $event);
    }
}
