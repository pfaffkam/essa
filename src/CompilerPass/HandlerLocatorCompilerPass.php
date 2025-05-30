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
        if (!$container->hasDefinition('essa.bus.projection')) {
            return;
        }

        $container->register('essa.bus.projection.handler_locator', ProjectionHandlerLocator::class)
            ->setAutowired(true)
            ->setDecoratedService('essa.bus.projection.messenger.handlers_locator')
            ->setArguments([
                new Reference('essa.bus.projection.handler_locator.inner'),
            ]);
    }
}
