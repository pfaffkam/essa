<?php

namespace PfaffKIT\Essa\EventSourcing;

use PfaffKIT\Essa\Shared\Identity;

interface ProjectionManagerInterface
{
    /**
     * Gets the repository for a projection class.
     *
     * @template T of Projection
     *
     * @param class-string<T> $projectionClass
     *
     * @return ProjectionRepository<T>
     */
    public function getRepository(string $projectionClass): ProjectionRepository;

    /**
     * Finds a projection by its ID.
     *
     * @template T of Projection
     *
     * @param class-string<T> $projectionClass
     *
     * @return T|null
     */
    public function find(string $projectionClass, Identity $id): ?Projection;

    /**
     * Finds all projections matching the criteria.
     *
     * @template T of Projection
     *
     * @param class-string<T> $projectionClass
     *
     * @return T[]
     */
    public function findBy(string $projectionClass, array $criteria): array;

    /**
     * Finds a single projection matching the criteria.
     *
     * @template T of Projection
     *
     * @param class-string<T> $projectionClass
     *
     * @return T|null
     */
    public function findOneBy(string $projectionClass, array $criteria): ?Projection;

    /**
     * Saves a projection.
     */
    public function save(Projection $projection): void;
}
