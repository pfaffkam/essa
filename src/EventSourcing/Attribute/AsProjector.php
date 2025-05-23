<?php

namespace PfaffKIT\Essa\EventSourcing\Attribute;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class AsProjector extends AsMessageHandler
{
    public function __construct(
        ?string $bus = 'essa.bus.event',
        ?string $fromTransport = null,
        ?string $handles = null,
        ?string $method = null,
        int $priority = 0)
    {
        parent::__construct($bus, $fromTransport, $handles, $method, $priority);
    }
}
