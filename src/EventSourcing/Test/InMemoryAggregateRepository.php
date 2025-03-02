<?php

namespace PfaffKIT\Essa\EventSourcing\Test;

use PfaffKIT\Essa\EventSourcing\Storage\AbstractAggregateRepository;

class InMemoryAggregateRepository extends AbstractAggregateRepository
{
    protected static string $type;

    public function __construct(string $classType)
    {
        parent::__construct(new InMemoryEventStorage());

        static::$type = &$classType;
    }

    public static function getType(): string
    {
        return static::$type;
    }
}
