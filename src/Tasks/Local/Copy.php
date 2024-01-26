<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Local;

use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;
use IsThereAnyDeal\Tools\Deby\Types\FileSet;

class Copy implements Task
{
    public function __construct(
        private readonly string $destination,
        private readonly FileSet $files
    ) {}

    public function run(Runtime $runtime): void {
        $destination = $this->destination;

        if (!file_exists($this->destination)) {
            mkdir($this->destination, recursive: true);
        }

        if (!is_dir($this->destination)) {
            throw new \ErrorException("{$this->destination} is not a directory");
        }

        $destination = $destination.DIRECTORY_SEPARATOR;

        foreach($this->files as $file) {
            $fileDestination = $destination.$this->files->getRelativePath($file);

            $targetDir = dirname($fileDestination);
            if (!file_exists($targetDir) || !is_dir($targetDir)) {
                mkdir($targetDir, recursive: true);
            }

            copy($file, $fileDestination);
        }
    }
}
