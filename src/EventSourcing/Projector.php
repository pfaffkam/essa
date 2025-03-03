<?php

namespace PfaffKIT\Essa\EventSourcing;

// Based on event stream creates or updates projection
interface Projector
{
    /** Creates new projection, or updates existing projection. */
    public function project(array $events, ?Projection $projection): Projection;

    /** Saves projection to storage */
    public function save(Projection $projection): void;

    /** List of events which projector subscribes to */
    public function subscribe(): array;

    /** Static type of projection which is supported by this projector */
    public static function projectionType(): string;
}
