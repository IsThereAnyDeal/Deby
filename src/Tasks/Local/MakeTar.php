<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Local;

use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;
use Phar;
use PharData;

class MakeTar implements Task
{
    public function __construct(
        private readonly string $name,
        private readonly string $dataDir,
        private readonly string $targetDir
    ) {}

    public function run(Runtime $runtime): void {
        $dataDir = realpath($this->dataDir);
        if ($dataDir === false || !is_dir($dataDir)) {
            throw new \ErrorException("{$this->dataDir} is not a directory");
        }

        $targetDir = realpath($this->targetDir);
        if ($targetDir === false || !is_dir($targetDir)) {
            throw new \ErrorException("{$this->targetDir} is not a directory");
        }

        $tarArchivePath = $this->targetDir.DIRECTORY_SEPARATOR.$this->name.".tar";

        $data = new PharData($tarArchivePath);
        $data->buildFromDirectory($this->dataDir);
        $result = $data->compress(Phar::GZ);

        unlink($tarArchivePath);

        // sage($result->getPath()); TODO log path?
    }
}
