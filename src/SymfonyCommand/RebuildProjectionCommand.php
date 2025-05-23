<?php

namespace PfaffKIT\Essa\SymfonyCommand;

use PfaffKIT\Essa\EventSourcing\Attribute\AsProjector;
use PfaffKIT\Essa\EventSourcing\Projection\Bus\ProjectionBus;
use PfaffKIT\Essa\EventSourcing\Projection\HandlerFilterStamp;
use PfaffKIT\Essa\EventSourcing\Projection\Projection;
use PfaffKIT\Essa\EventSourcing\Projection\ProjectionManagerInterface;
use PfaffKIT\Essa\EventSourcing\Projection\Projector;
use PfaffKIT\Essa\EventSourcing\Storage\EventStorage;
use PfaffKIT\Essa\Shared\Id;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'essa:projection:rebuild',
    description: 'Rebuild projections based on event store.'
)]
class RebuildProjectionCommand extends Command
{
    public function __construct(
        private readonly EventStorage $eventStorage,
        private readonly ProjectionBus $projectionBus,
        private readonly ProjectionManagerInterface $projectionManager,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('projection-class', InputArgument::OPTIONAL, 'The projection class to rebuild (fully qualified class name)')
            ->addArgument('aggregate-id', InputArgument::OPTIONAL, 'The ID of the aggregate to rebuild projection for')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Rebuild all projections')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force rebuild without confirmation')
            ->addOption('batch-size', 'b', InputOption::VALUE_OPTIONAL, 'Number of events to process in a single batch', 100)
        ;
    }

    /**
     * @throws \ReflectionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectionClass = $input->getArgument('projection-class');
        $aggregateId = $input->getArgument('aggregate-id');
        $rebuildAll = $input->getOption('all');
        $force = $input->getOption('force');
        $batchSize = max(1, (int) $input->getOption('batch-size'));

        if (!$projectionClass && !$rebuildAll) {
            $io->error('You must specify either a projection class or use --all option');

            return Command::INVALID;
        }

        if ($aggregateId && $rebuildAll) {
            $io->error('Cannot specify both aggregate ID and --all option');

            return Command::INVALID;
        }

        $projectionClasses = $projectionClass ? [$projectionClass] : [];

        foreach ($projectionClasses as $class) {
            if (!class_exists($class) || !is_subclass_of($class, Projection::class)) {
                $io->warning(sprintf('Skipping invalid projection class: %s', $class));
                continue;
            }

            $confirmationMessage = $aggregateId
                ? sprintf('Are you sure you want to rebuild projection <fg=bright-blue>%s</> limited by aggregate id <fg=bright-blue>%s</>?', $class, $aggregateId)
                : sprintf('Are you sure you want to rebuild all projections of type <fg=bright-blue>%s</>?', $class);

            if (!$force && !$io->confirm($confirmationMessage, false)) {
                $io->note(sprintf('Skipping projection: %s', $class));
                continue;
            }

            $io->section(sprintf("Rebuilding projection:\nFQCN: %s\nAGID: <fg=bright-blue>%s</>",
                $this->highlightFQCN($class),
                $aggregateId ?: '*'
            ));
            $this->rebuildProjection($class, $io, $aggregateId, $batchSize);
        }

        $io->success('Projection rebuild completed');

        return Command::SUCCESS;
    }

    /**
     * @param class-string<Projection> $projectionClass
     *
     * @throws \ReflectionException
     */
    private function rebuildProjection(string $projectionClass, SymfonyStyle $io, ?string $aggregateId = null, int $batchSize = self::BATCH_SIZE): void
    {
        $projectorClass = $projectionClass::getProjectorClass();

        if (!$projectorClass) {
            $io->warning(sprintf('No projector found for projection class: %s', $projectionClass));

            return;
        }

        $io->writeln(sprintf("Following projector will be used: <fg=bright-blue>%s</>\n", $this->highlightFQCN($projectorClass)));

        $eventTypes = $this->extractEventTypes($projectorClass);
        $io->writeln(sprintf('This projector subscribes to <fg=bright-blue>%d</> event types:', count($eventTypes)));
        foreach ($eventTypes as $eventType) {
            $io->writeln(sprintf('  - <fg=bright-blue>%s</>', $this->highlightFQCN($eventType)));
        }
        $io->writeln('');

        // Clear existing projections
        $io->writeln($aggregateId
            ? sprintf('Removing existing projections for aggregate <fg=bright-blue>%s</>...', $aggregateId)
            : 'Removing existing projections...'
        );
        $count = $this->clearProjections($projectionClass, $io, $aggregateId);
        $io->writeln(sprintf('<fg=bright-green>✓</> Removed <fg=yellow>%d</> projections', $count));
        $io->writeln('');

        $io->section('Rebuilding projections...');

        // If aggregate ID is provided, only process events for that aggregate
        $eventStream = $aggregateId
            ? $this->eventStorage->loadInBatches(0, $batchSize, $eventTypes, [Id::fromString($aggregateId)])
            : $this->eventStorage->loadInBatches(0, $batchSize, $eventTypes);

        $eventCount = $aggregateId
            ? $this->eventStorage->count($eventTypes, [Id::fromString($aggregateId)])
            : $this->eventStorage->count($eventTypes);

        $progress = $io->createProgressBar($eventCount);
        $progress->setFormat('verbose');

        foreach ($eventStream as $events) {
            $events = is_array($events) ? $events : [$events]; // Handle both single and batch events

            $this->projectionBus->dispatchStamped(
                [new HandlerFilterStamp($projectorClass)],
                ...$events
            );

            $progress->advance(count($events));
        }

        $progress->finish();
        $io->newLine(2);
    }

    private function highlightFQCN(string $fqcn, string $pathColor = 'bright-blue', string $classColor = 'cyan'): string
    {
        $parts = explode('\\', $fqcn);
        $namespace = implode('\\', array_slice($parts, 0, count($parts) - 1));
        $className = $parts[count($parts) - 1];

        return sprintf("<fg=%s>%s\\\e</><fg=%s>%s</>", $pathColor, $namespace, $classColor, $className);
    }

    /**
     * @param class-string<Projector> $projector
     *
     * @throws \ReflectionException
     */
    private function extractEventTypes(string $projector): array
    {
        $reflection = new \ReflectionClass($projector);

        // find methods which has attribute #[AsProjector]
        $eventTypes = [];
        foreach ($reflection->getMethods() as $method) {
            $attributes = $method->getAttributes(AsProjector::class);
            if (0 == count($attributes)) {
                continue;
            }

            $eventTypes[] = $method->getParameters()[0]->getType()->getName();
        }

        return array_unique($eventTypes);
    }

    /**
     * Clear all projections of the given type.
     */
    private function clearProjections(string $projectionClass, SymfonyStyle $io, ?string $aggregateId = null): int
    {
        $criteria = [];

        if ($aggregateId) {
            $criteria['_id'] = $aggregateId;
        }

        return $this->projectionManager->deleteBy($projectionClass, $criteria);
    }
}
