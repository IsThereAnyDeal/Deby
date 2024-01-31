<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Local;

use FilesystemIterator;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

class DeleteDir implements Task
{
    public function __construct(
        private readonly string $dir,
        private readonly bool $dryRun=false
    ) {}

    private function rmdir(string $dir): void {
        $iterator = new FilesystemIterator($dir,
              FilesystemIterator::SKIP_DOTS
            | FilesystemIterator::CURRENT_AS_PATHNAME
        );

        foreach($iterator as $file) {
            if (is_dir($file)) {
                $this->rmdir($file);
            } else {
                if ($this->dryRun) {
                    echo "Delete: {$file}\n";
                } else {
                    unlink($file);
                }
            }
        }

        if ($this->dryRun) {
            echo "Delete {$dir}\n";
        } else {
            rmdir($dir);
        }
    }

    public function run(Runtime $runtime): void {
        $dir = realpath($this->dir);
        if ($dir !== false && !is_dir($dir)) {
            throw new \ErrorException("{$this->dir} is not dir");
        }

        $this->rmdir($dir);
    }
}
