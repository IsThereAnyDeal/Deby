<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Local;

use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

class ExecTask implements Task
{
    public function __construct(
        private readonly string $command,
        private readonly bool   $printOutput=true,
        private readonly bool   $printOnError=true,
        private readonly bool   $throwOnError=true
    ) {}

    public function run(Runtime $runtime): void {
        $output = [];
        exec($this->command, $output, $resultCode);

        if (!empty($output)) {
            if ($this->printOutput || ($resultCode !== 0 && $this->printOnError)) {
                echo implode("\n", $output);
                echo "\n";
            }
        }

        if ($resultCode !== 0 && $this->throwOnError) {
            throw new \ErrorException();
        }
    }
}
