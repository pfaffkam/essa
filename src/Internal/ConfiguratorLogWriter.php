<?php

namespace PfaffKIT\Essa\Internal;

use Symfony\Component\Console\Style\SymfonyStyle;

readonly class ConfiguratorLogWriter
{
    public function __construct(
        private string $configuratorType,
        private SymfonyStyle $io,
    ) {}

    public function info(string $message): void
    {
        $this->io->writeln(sprintf("\t <fg=blue>info</>  %s\n", $message));
    }

    public function tip(array $messages): void
    {
        // first message process
        $this->io->writeln(sprintf("\t <fg=green;options=bold>tip</>   %s", array_shift($messages)));

        // rest of the messages
        foreach ($messages as $message) {
            $this->io->writeln(sprintf("\t       %s", $message));
        }
        $this->io->write("\n");
    }

    public function ask(string $question, ?string $default = null, ?callable $validator = null): string
    {
        while (true) {
            $response = $this->io->ask(sprintf("\t <fg=yellow>q</>     %s", $question), $default);

            if (null === $validator || $validator($response)) {
                break;
            }

            $this->io->writeln("\t <fg=red>error</> Invalid input, please try again.");
        }

        return $response;
    }
}
