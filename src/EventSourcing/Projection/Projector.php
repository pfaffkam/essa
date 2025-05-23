<?php

namespace PfaffKIT\Essa\EventSourcing\Projection;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: [Projector::class])]
interface Projector
{
    public function load(AggregateEvent $event): ?Projection;

    public function save(Projection $projection): void;
}
