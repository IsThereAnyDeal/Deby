<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Local;

use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;
use IsThereAnyDeal\Tools\Deby\Types\FileSet;

class DeleteFiles implements Task
{
    public function __construct(
        private readonly FileSet $files,
        private readonly bool $dryRun=false
    ) {}

    public function run(Runtime $runtime): void {
        foreach($this->files as $path) {
            if ($this->dryRun) {
                echo "Delete {$path}\n";
            } else {
                unlink($path);
            }
        }
    }
}
