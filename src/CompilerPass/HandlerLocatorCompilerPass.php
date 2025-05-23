<?php

namespace PfaffKIT\Essa\CompilerPass;

use PfaffKIT\Essa\EventSourcing\Projection\ProjectionHandlerLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class HandlerLocatorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('essa.bus.event')) {
            return;
        }

        $container->register('essa.bus.event.handler_locator', ProjectionHandlerLocator::class)
            ->setAutowired(true)
            ->setDecoratedService('essa.bus.event.messenger.handlers_locator')
            ->setArguments([
                new Reference('essa.bus.event.handler_locator.inner'),
            ]);
    }
}
