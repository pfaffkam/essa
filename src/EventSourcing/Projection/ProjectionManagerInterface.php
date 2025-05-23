<?php

namespace PfaffKIT\Essa\EventSourcing\Projection;

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
     * @return Projection<T>|null
     */
    public function find(string $projectionClass, Identity $id): ?Projection;

    /**
     * Finds all projections matching the criteria.
     *
     * @template T of Projection
     *
     * @param class-string<T> $projectionClass
     *
     * @return Projection<T>[]
     */
    public function findBy(string $projectionClass, array $criteria): array;

    /**
     * Finds a single projection matching the criteria.
     *
     * @template T of Projection
     *
     * @param class-string<T> $projectionClass
     *
     * @return Projection<T>|null
     */
    public function findOneBy(string $projectionClass, array $criteria): ?Projection;

    /**
     * Removes projections by the given criteria.
     * Returns the number of removed projections.
     *
     * @param class-string<Projection> $projectionClass
     */
    public function deleteBy(string $projectionClass, array $criteria): int;

    /**
     * Saves a projection.
     */
    public function save(Projection $projection): void;
}
