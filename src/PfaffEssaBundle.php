<?php

namespace PfaffKIT\Essa;

use PfaffKIT\Essa\CompilerPass\EventResolverCompilerPass;
use PfaffKIT\Essa\CompilerPass\HandlerLocatorCompilerPass;
use PfaffKIT\Essa\EventSourcing\EventUpcaster;
use PfaffKIT\Essa\EventSourcing\Projection\ProjectionRepository;
use PfaffKIT\Essa\Internal\ExtensionConfig;
use PfaffKIT\Essa\Query\QueryHandler;
use PfaffKIT\Essa\Query\ValidateQueryMiddleware;
use PfaffKIT\Essa\Shared\CommandHandler;
use PfaffKIT\Essa\Shared\EventListener;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class PfaffEssaBundle extends AbstractBundle
{
    protected string $name = 'EssaBundle';
    protected string $alias = 'essa';

    public const string CONFIG_FILE = 'config/packages/essa.yaml';

    private const array CONFIGURATORS = [
        'PfaffKIT\Essa\Adapters\Storage\Config\EntityConfigurator',
        'PfaffKIT\Essa\Adapters\StorageMongo\Config\EnvironmentConfigurator',
    ];

    private const array CONFIGS = [
        'PfaffKIT\Essa\Adapters\Storage\Config\Config',
        'PfaffKIT\Essa\Adapters\StorageMongo\Config\Config',
    ];

    public function configure(DefinitionConfigurator $definition): void
    {
        $rootNode = $definition->rootNode()->children();

        $rootNode
            ->scalarNode('default_event_storage')->defaultNull()->end()
            ->scalarNode('default_snapshot_storage')->defaultNull()->end();

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

        // Load snapshot storage
        if ($config['default_snapshot_storage']) {
            $container->services()
                ->get('essa.snapshot_storage')
                ->class($config['default_snapshot_storage']);
        }

        $this->loadConfigs($container, $builder, $config);
        $this->loadConfigurators($container);
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('framework', [
            'messenger' => [
                'buses' => [
                    'essa.bus.event' => [
                        'default_middleware' => [
                            'enabled' => true,
                            'allow_no_handlers' => false,
                        ],
                    ],
                    'essa.bus.projection' => [
                        'default_middleware' => [
                            'enabled' => true,
                            'allow_no_handlers' => true,
                        ],
                    ],
                    'essa.bus.command' => [
                        'default_middleware' => [
                            'enabled' => true,
                            'allow_no_handlers' => false,
                        ],
                        'middleware' => [
                            'doctrine_transaction',
                        ],
                    ],
                    'essa.bus.query' => [
                        'middleware' => [
                            ValidateQueryMiddleware::class,
                        ],
                        'default_middleware' => [
                            'enabled' => true,
                            'allow_no_handlers' => false,
                        ],
                    ],

                    'essa.bus.integration_event' => [
                        'default_middleware' => [
                            'enabled' => true,
                            'allow_no_handlers' => true,
                        ],
                    ],
                ],
            ],
        ]);

        $builder->prependExtensionConfig('monolog', [
            'channels' => [
                'essa',
            ],
        ]);

        $this->prependConfigs($container, $builder);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerForAutoconfiguration(ProjectionRepository::class)
            ->addTag(ProjectionRepository::class);

        $container->registerForAutoconfiguration(EventUpcaster::class)
            ->addTag(EventUpcaster::class);

        $container->registerForAutoconfiguration(CommandHandler::class)
            ->addTag('messenger.message_handler', ['bus' => 'essa.bus.command']);

        $container->registerForAutoconfiguration(QueryHandler::class)
            ->addTag('messenger.message_handler', ['bus' => 'essa.bus.query']);

        $container->registerForAutoconfiguration(EventListener::class)
            ->addTag('messenger.message_handler', ['bus' => 'essa.bus.event']);

        $container->addCompilerPass(new EventResolverCompilerPass());
        $container->addCompilerPass(new HandlerLocatorCompilerPass());
    }

    private function prependConfigs(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Prepend all addons
        foreach (self::CONFIGS as $config) {
            if (!class_exists($config)) {
                continue;
            }

            $config::prependExtension($container, $builder);
        }
    }

    private function loadConfigs(ContainerConfigurator $container, ContainerBuilder $containerBuilder, array $bundleConfig): void
    {
        foreach (self::CONFIGS as $config) {
            if (!class_exists($config)) {
                continue;
            }

            $serviceId = 'essa.extension-config.'.$config::getExtensionName();

            // Register the service
            $container->services()
                ->set($serviceId, $config)
                ->public()
                ->factory([$config, 'instantiate'])
                ->args([$bundleConfig['extensions'][$config::getExtensionName()] ?? []]);

            // Set up the alias
            $container->services()
                ->alias($config, $serviceId)
                ->public();

            // instantiate the config and call loadExtension
            $config::loadExtension($bundleConfig['extensions'][$config::getExtensionName()] ?? [], $container, $containerBuilder);
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
