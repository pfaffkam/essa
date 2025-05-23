<?php

namespace PfaffKIT\Essa\EventSourcing\Projection;

use PfaffKIT\Essa\Shared\Identity;

interface ProjectionRepository
{
    public function save(Projection $projection): void;

    public function getById(Identity $id): ?Projection;

    /** @return Projection[] */
    public function findBy(array $criteria): array;

    public function findOneBy(array $criteria): ?Projection;

    public function deleteBy(array $criteria): int;

    /**
     * Returns the FQCN of the Projection class this repository handles.
     *
     * @return class-string<Projection>
     */
    public static function getProjectionClass(): string;
}
