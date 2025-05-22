<?php

namespace PfaffKIT\Essa\EventSourcing;

use PfaffKIT\Essa\Shared\Identity;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: [ProjectionRepository::class])]
interface ProjectionRepository
{
    public function save(Projection $projection): void;

    public function getById(Identity $id): ?Projection;

    /** @return Projection[] */
    public function findBy(array $criteria): array;

    public function findOneBy(array $criteria): ?Projection;

    public static function projectionType(): string;
}
