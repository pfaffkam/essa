<?php

namespace PfaffKIT\Essa\EventSourcing\Projection;

use PfaffKIT\Essa\Shared\Identity;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class ProjectionManager implements ProjectionManagerInterface
{
    /** @var array<string, ProjectionRepository> */
    private array $repositoriesByProjection = [];

    /**
     * @param iterable<ProjectionRepository> $repositories
     */
    public function __construct(
        #[AutowireIterator(ProjectionRepository::class)]
        iterable $repositories,
    ) {
        foreach ($repositories as $repository) {
            $projection = $repository::getProjectionClass();
            $this->repositoriesByProjection[$projection] = $repository;
        }
    }

    /**
     * Gets the repository for a projection class.
     *
     * @template T of Projection
     *
     * @param class-string<T> $projectionClass
     *
     * @return ProjectionRepository<T>
     */
    public function getRepository(string $projectionClass): ProjectionRepository
    {
        if (!is_subclass_of($projectionClass, Projection::class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" is not a valid Projection class. It must implement %s', $projectionClass, Projection::class));
        }

        if (!isset($this->repositoriesByProjection[$projectionClass])) {
            throw new \RuntimeException(sprintf('No repository found for projection "%s"', $projectionClass));
        }

        return $this->repositoriesByProjection[$projectionClass];
    }

    /**
     * Finds a projection by its ID.
     *
     * @template T of Projection
     *
     * @param class-string<T> $projectionClass
     *
     * @return Projection<T>|null
     */
    public function find(string $projectionClass, Identity $id): ?Projection
    {
        return $this->getRepository($projectionClass)->getById($id);
    }

    /**
     * Finds all projections matching the criteria.
     *
     * @template T of Projection
     *
     * @param class-string<T> $projectionClass
     *
     * @return Projection<T>[]
     */
    public function findBy(string $projectionClass, array $criteria): array
    {
        return $this->getRepository($projectionClass)->findBy($criteria);
    }

    /**
     * Finds a single projection matching the criteria.
     *
     * @template T of Projection
     *
     * @param class-string<T> $projectionClass
     *
     * @return Projection<T>|null
     */
    public function findOneBy(string $projectionClass, array $criteria): ?Projection
    {
        return $this->getRepository($projectionClass)->findOneBy($criteria);
    }

    public function deleteBy(string $projectionClass, array $criteria): int
    {
        return $this->getRepository($projectionClass)->deleteBy($criteria);
    }

    /**
     * Saves a projection.
     */
    public function save(Projection $projection): void
    {
        $repository = $this->getRepository(get_class($projection));
        $repository->save($projection);
    }
}
