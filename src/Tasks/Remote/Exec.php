<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Cli\Cli;
use IsThereAnyDeal\Tools\Deby\Cli\Color;
use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

#[Remote]
class Exec implements Task
{
    public function __construct(
        private readonly string $command,
        private readonly bool $printOutput=true,
        private readonly bool $printOnError=true,
        private readonly bool $throwOnError=true
    ) {}

    #[\Override]
    public function run(Runtime $runtime): void {
        $ssh = $runtime->getActiveConnection()->getSshClient();

        $stdout = "";
        $stderr = "";
        $success = $ssh->exec($this->command, $stdout, $stderr, $this->throwOnError);

        if (!empty($stdout) && $this->printOutput) {
            Cli::writeLn($stdout, Color::Red);
        }

        if (!empty($stderr) && $this->printOnError) {
            Cli::writeLn($stderr, Color::Red);
        }

        if (!$success && $this->throwOnError) {
            throw new \ErrorException();
        }
    }
}
