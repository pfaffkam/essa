<?php

namespace PfaffKIT\Essa\Tests\mocks;

use PfaffKIT\Essa\EventSourcing\Projection\Projection;
use PfaffKIT\Essa\EventSourcing\Projection\ProjectionRepository;
use PfaffKIT\Essa\Shared\Identity;

class TestProjectionRepository implements ProjectionRepository
{
    /** @var array<string, Projection> */
    private array $projections = [];
    private ?Projection $lastSavedProjection = null;

    public function addProjection(Projection $projection): void
    {
        $this->projections[(string) $projection->id] = $projection;
    }

    public function getLastSavedProjection(): ?Projection
    {
        return $this->lastSavedProjection;
    }

    public static function getProjectionClass(): string
    {
        return TestProjection::class;
    }

    public function save(Projection $projection): void
    {
        $this->lastSavedProjection = $projection;
        $this->projections[(string) $projection->id] = $projection;
    }

    public function getById(Identity $id): ?Projection
    {
        return $this->projections[(string) $id] ?? null;
    }

    public function findBy(array $criteria): array
    {
        if (isset($criteria['id'])) {
            $searchId = $criteria['id'];

            return array_values(array_filter(
                $this->projections,
                fn (Projection $p) => $p->id->toBinary() === $searchId
            ));
        }

        return array_values($this->projections);
    }

    public function findOneBy(array $criteria): ?Projection
    {
        $results = $this->findBy($criteria);

        return $results[0] ?? null;
    }

    public function deleteBy(array $criteria): int
    {
        $count = 0;

        if (isset($criteria['id'])) {
            $searchId = $criteria['id'];
            foreach ($this->projections as $key => $projection) {
                if ($projection->id->toBinary() === $searchId) {
                    unset($this->projections[$key]);
                    ++$count;
                }
            }
        } else {
            $count = count($this->projections);
            $this->projections = [];
        }

        return $count;
    }
}
