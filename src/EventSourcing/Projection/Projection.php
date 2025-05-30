<?php

namespace PfaffKIT\Essa\EventSourcing\Projection;

use PfaffKIT\Essa\Shared\Identity;

// Real object with base data, stored in mongodb or another database.
interface Projection
{
    /** Identity of projection - it can be aggregate id in simple cases. */
    public Identity $id { get; }

    /** String name of storage to allow refactoring  */
    public static function getProjectionName(): string;

    /**
     * Returns the FQCN of the Projector class for this projection.
     *
     * @return class-string<Projector>
     */
    public static function getProjectorClass(): string;

    /**
     * Returns the FQCN of the Repository class for this projection.
     *
     * @return class-string<ProjectionRepository>
     */
    public static function getRepositoryClass(): string;
}
