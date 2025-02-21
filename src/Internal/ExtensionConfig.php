<?php

namespace PfaffKIT\Essa\Internal;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

abstract class ExtensionConfig
{
    abstract public static function getExtensionName(): string;

    abstract public static function configure(NodeBuilder $nodeBuilder): void;

    public static function generateDefaultConfig(): array
    {
        $nodeBuilder = new ArrayNodeDefinition(static::getExtensionName())->children();
        static::configure($nodeBuilder);

        $defaultConfig = [];

        foreach ($nodeBuilder->end()->getNode(true)->getChildren() as $child) {
            $defaultConfig[$child->getName()] = $child->getDefaultValue();
        }

        return $defaultConfig;
    }
}
