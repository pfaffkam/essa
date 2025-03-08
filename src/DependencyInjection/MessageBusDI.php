<?php

namespace PfaffKIT\Essa\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Messenger\MessageBus;

class MessageBusDI
{
    public static function registerEventBus(string $busName, ContainerBuilder $builder): void
    {
        $builder->register($busName, MessageBus::class)
            ->addArgument([])
            ->addTag('messenger.bus');

        $middleware = [
            ['id' => 'add_bus_name_stamp_middleware', 'arguments' => [$busName]],
            ['id' => 'send_message'],
            ['id' => 'handle_message'],
            ['id' => 'doctrine_transaction'],
            ['id' => 'failed_message_processing_middleware'],
        ];

        $builder->setParameter($busName.'.event.middleware', $middleware);
    }
}
