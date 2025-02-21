<?php

namespace PfaffKIT\Essa\Internal;

use PfaffKIT\Essa\PfaffEssaBundle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[AsCommand(
    name: 'essa:configure',
    description: 'Initiate ESSA and related libraries'
)]
class ConfiguratorCommand extends Command
{
    public function __construct(
        private ContainerInterface $container,
        private iterable $configurators,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('ESSA Configuration');
        $io->writeln('Beginning ESSA configuration...');

        /**
         * @var Configurator $configurator
         */
        foreach ($this->configurators as $configurator) {
            if (!$configurator->shouldConfigure()) {
                $io->writeln('Configuration <fg=white>'.get_class($configurator).'</> <fg=green> configuration OK</>.');
                continue;
            }
            $io->writeln('>>> <fg=white>'.get_class($configurator).'</> <fg=yellow>configuration required</> - proceeding...');
            $this->runConfigurator($configurator, $io);
            $io->writeln('<<< <fg=white>'.get_class($configurator).'</> <fg=green;options=bold>configuration DONE</>.');
        }

        $io->success('ESSA configuration completed.');

        return 0;
    }

    private function runConfigurator(Configurator $configurator, SymfonyStyle $io): void
    {
        $config = $this->container->get('essa.extension-config.'.$configurator::getExtensionName());

        $configFileModifier = new ConfigFileModifier(PfaffEssaBundle::CONFIG_FILE);
        $extensionConfig = $configFileModifier->extractExtensionConfig($configurator::getExtensionName()) ?? $config->generateDefaultConfig();

        $configurator->configure(
            new ConfiguratorLogWriter(get_class($configurator), $io),
            $extensionConfigChanger = new ExtensionConfigChanger($extensionConfig)
        );

        $configFileModifier->setExtensionConfig($configurator::getExtensionName(), $extensionConfigChanger->result());
        $configFileModifier->flush();
    }
}
