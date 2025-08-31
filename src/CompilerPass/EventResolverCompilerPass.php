<?php

namespace PfaffKIT\Essa\CompilerPass;

use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EventResolverCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('essa.event_class_resolver');

        $definition->setArgument('$eventClasses', $this->getClassesImplementingInterface());
    }

    private function getClassesImplementingInterface(): array
    {
        // This loads only used classes, but it should not be a problem.
        // Instead, we can use RecursiveIterators to scan all files and determine implementations.
        $allClasses = get_declared_classes();

        $eventClasses = [];
        foreach ($allClasses as $class) {
            $reflectionClass = new \ReflectionClass($class);
            if ($reflectionClass->isSubclassOf(AggregateEvent::class)
             && !$reflectionClass->isAbstract()) {
                $eventClasses[] = $class;
            }
        }

        return $eventClasses;
    }
}
