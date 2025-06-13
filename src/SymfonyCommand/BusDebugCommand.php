<?php

namespace PfaffKIT\Essa\SymfonyCommand;

use PfaffKIT\Essa\Command\Command as CommandInterface;
use PfaffKIT\Essa\EventSourcing\AggregateEvent;
use PfaffKIT\Essa\Query\Query;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'essa:debug:bus',
    description: 'Debug message handlers for all buses'
)]
class BusDebugCommand extends Command
{
    private const array BUS_CONFIGURATION = [
        'essa.bus.command' => CommandInterface::class,
        'essa.bus.query' => Query::class,
        'essa.bus.projection' => AggregateEvent::class, // p
        'essa.bus.event' => AggregateEvent::class,
    ];

    public function __construct(
        private array $mapping,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('bus', InputArgument::OPTIONAL, 'The bus service ID to debug (e.g., "command.bus" or "event.bus")')
            ->addArgument('filter', InputArgument::OPTIONAL, 'Filter messages by this string')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Messenger Buses and their Handlers');

        $mapping = $this->mapping;
        if ($bus = $input->getArgument('bus')) {
            isset($mapping[$bus]) ? $mapping = [$bus => $mapping[$bus]] : throw new \RuntimeException('eeee');
        }

        $mapping = $this->filterMappingsByInterfaces($mapping);
        $mapping = $this->filterMappingsByRegexp($mapping, $input->getArgument('filter'));

        foreach ($mapping as $bus => $handlersByMessage) {
            $io->writeln(sprintf('<fg=red;options=bold>%s</>', $bus));

            foreach ($handlersByMessage as $message => $handlers) {
                $io->writeln(
                    sprintf('  %s', CommandUtils::highlightFQCN($message, classOption: 'bold'))
                );
                foreach ($handlers as $handler) {
                    if (!isset($handler[1]['bus'])) {
                        continue;
                    }

                    if ($handler[1]['bus'] != $bus) {
                        continue;
                    }

                    $io->writeln(
                        sprintf('    > %s', CommandUtils::highlightFQCN($handler[0]))
                    );
                }
                $io->writeln('');
            }
        }

        return Command::SUCCESS;
    }

    private function filterMappingsByRegexp(array $mapping, ?string $regexp): array
    {
        if (!$regexp) {
            return $mapping;
        }

        foreach ($mapping as $bus => $handlersByMessage) {
            foreach ($handlersByMessage as $message => $handlers) {
                if (preg_match($regexp, $message) || array_any($handlers, fn ($handler) => preg_match($regexp, $handler[0]))) {
                    continue;
                }
                unset($mapping[$bus][$message]);
            }
        }

        return $mapping;
    }

    private function filterMappingsByInterfaces(array $mapping): array
    {
        foreach ($mapping as $bus => $handlersByMessage) {
            // if bus is not one from essa, just drop it
            if (!in_array($bus, array_keys(self::BUS_CONFIGURATION))) {
                unset($mapping[$bus]);
                continue;
            }

            $messageInterface = $this->getMessageInterfaceByBus($bus);
            foreach ($handlersByMessage as $message => $handlers) {
                $reflection = new \ReflectionClass($message);
                if (!$reflection->implementsInterface($messageInterface)) {
                    unset($mapping[$bus][$message]);
                    continue;
                }
            }
        }

        return $mapping;
    }

    private function getMessageInterfaceByBus(string $busName): string
    {
        return self::BUS_CONFIGURATION[$busName] ?: '';
    }
}
