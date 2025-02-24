<?php

namespace PfaffKIT\Essa;

use PfaffKIT\Essa\CompilerPass\EventResolverCompilerPass;
use PfaffKIT\Essa\Internal\ExtensionConfig;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class PfaffEssaBundle extends AbstractBundle
{
    protected string $name = 'EssaBundle';

    public const string CONFIG_FILE = 'config/packages/essa.yaml';

    private const array CONFIGURATORS = [
        'PfaffKIT\Essa\Adapters\Storage\Config\EntityConfigurator',
    ];

    private const array CONFIGS = [
        'PfaffKIT\Essa\Adapters\Storage\Config\Config',
    ];

    public function configure(DefinitionConfigurator $definition): void
    {
        $rootNode = $definition->rootNode()->children();

        $rootNode
            ->scalarNode('default_event_storage')->defaultNull()->end();
        // Load extension configs
        $extensionsNode = $rootNode->arrayNode('extensions')->addDefaultsIfNotSet()->children();
        /** @var ExtensionConfig $config */
        foreach (self::CONFIGS as $config) {
            $config::configure(
                $extensionsNode->arrayNode($config::getExtensionName())->addDefaultsIfNotSet()->children()
            );
        }
        $extensionsNode->end();
        $rootNode->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $loader = new YamlFileLoader($builder, new FileLocator($this->getPath().'/config'));
        $loader->load('services.yaml');

        // Load event storage
        if ($config['default_event_storage']) {
            $container->services()
                ->get('essa.event_storage')
                ->class($config['default_event_storage']);
        }

        $this->loadConfigs($container, $builder, $config);
        $this->loadConfigurators($container);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new EventResolverCompilerPass());
    }

    private function loadConfigs(ContainerConfigurator $container, ContainerBuilder $containerBuilder, array $bundleConfig): void
    {
        foreach (self::CONFIGS as $config) {
            if (!class_exists($config)) {
                continue;
            }

            $container->services()
                ->set('essa.extension-config.'.$config::getExtensionName(), $config)
                ->factory([$config, 'instantiate'])->args([$bundleConfig['extensions'][$config::getExtensionName()]])
                ->alias($config, 'essa.extension-config.'.$config::getExtensionName());
        }
    }

    private function loadConfigurators(ContainerConfigurator $container): void
    {
        foreach (self::CONFIGURATORS as $configurator) {
            if (!class_exists($configurator)) {
                continue;
            }

            $container->services()
                ->set($configurator)
                ->autowire()
                ->tag('essa.configurator');
        }
    }
}
