<?php

namespace PfaffKIT\Essa\SymfonyCommand;

use PfaffKIT\Essa\EventSourcing\Attribute\AsProjector;
use PfaffKIT\Essa\EventSourcing\Projection\Bus\ProjectionBus;
use PfaffKIT\Essa\EventSourcing\Projection\HandlerFilterStamp;
use PfaffKIT\Essa\EventSourcing\Projection\Projection;
use PfaffKIT\Essa\EventSourcing\Projection\Projector;
use PfaffKIT\Essa\EventSourcing\Storage\EventStorage;
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
    private const int BATCH_SIZE = 100;

    public function __construct(
        private readonly EventStorage $eventStorage,
        private readonly ProjectionBus $projectionBus,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('projection-class', InputArgument::OPTIONAL, 'The projection class to rebuild (fully qualified class name)')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Rebuild all projections')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force rebuild without confirmation')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectionClass = $input->getArgument('projection-class');
        $rebuildAll = $input->getOption('all');
        $force = $input->getOption('force');

        if (!$projectionClass && !$rebuildAll) {
            $io->error('You must specify either a projection class or use --all option');

            return Command::INVALID;
        }

        $projectionClasses = [$projectionClass];

        foreach ($projectionClasses as $class) {
            if (!class_exists($class) || !is_subclass_of($class, Projection::class)) {
                $io->warning(sprintf('Skipping invalid projection class: %s', $class));
                continue;
            }

            if (!$force && !$io->confirm(sprintf('Are you sure you want to rebuild projection %s?', $class), false)) {
                $io->note(sprintf('Skipping projection: %s', $class));
                continue;
            }

            $io->section(sprintf('Rebuilding projection: %s', $class));
            $this->rebuildProjection($class, $io);
        }

        $io->success('Projection rebuild completed');

        return Command::SUCCESS;
    }

    /**
     * @param class-string<Projection> $projectionClass
     */
    private function rebuildProjection(string $projectionClass, SymfonyStyle $io): void
    {
        $projectorClass = $projectionClass::getProjectorClass();

        if (!$projectorClass) {
            $io->warning(sprintf('No projector found for projection class: %s', $projectionClass));

            return;
        }

        $io->note(sprintf('Using projector: %s', $projectorClass));

        // Get event types this projector is interested in
        $eventTypes = $this->extractEventTypes($projectorClass);
        $io->note(sprintf('Projector subscribes to %d event types', count($eventTypes)));

        // Clear existing projections
        $io->note('Clearing existing projections...');
        $this->clearProjections($projectionClass, $io);

        $io->section('Processing events...');

        $progress = $io->createProgressBar();
        $progress->setFormat('debug');

        foreach ($this->eventStorage->loadInBatches(0, self::BATCH_SIZE, $eventTypes) as $events) {
            $this->projectionBus->dispatchStamped(
                [new HandlerFilterStamp($projectorClass)],
                ...$events
            );
        }

        $progress->finish();
        $io->newLine(2);

        $io->success('all done');
    }

    private function findProjectorForProjection(string $projectionClass): ?Projector
    {
        foreach ($this->projectors as $projector) {
            if ($projector::projectionType() === $projectionClass) {
                return $projector;
            }
        }

        return null;
    }

    /**
     * Get the projection class from a projector instance.
     */
    private function getProjectionClassFromProjector(Projector $projector): ?string
    {
        $reflection = new \ReflectionClass($projector);

        // Try to get projection class from method return type
        try {
            $projectMethod = $reflection->getMethod('project');
            $returnType = $projectMethod->getReturnType();

            if ($returnType && !$returnType->isBuiltin()) {
                $projectionClass = $returnType->getName();
                if (is_a($projectionClass, Projection::class, true)) {
                    return $projectionClass;
                }
            }
        } catch (\ReflectionException $e) {
            // Method not found, try other approaches
        }

        // Try to get from class name convention
        $className = $reflection->getShortName();
        if (str_ends_with($className, 'Projector')) {
            $projectionClass = substr($className, 0, -9); // Remove 'Projector' suffix
            $namespace = str_replace('\\Application\\', '\\Core\\', $reflection->getNamespaceName());
            $projectionClass = $namespace.'\\'.$projectionClass.'\\'.$projectionClass.'Projection';

            if (class_exists($projectionClass) && is_a($projectionClass, Projection::class, true)) {
                return $projectionClass;
            }
        }

        return null;
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
    private function clearProjections(string $projectionClass, SymfonyStyle $io): void
    {
        //        try {
        //            $this->projectionManager->deleteAll($projectionClass);
        //            $io->note('Existing projections cleared successfully');
        //        } catch (\Exception $e) {
        //            $io->warning(sprintf('Error clearing projections: %s', $e->getMessage()));
        //        }
    }
}
