<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Local;

use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

class WriteFile implements Task
{
    public function __construct(
        private readonly string $path,
        private readonly string $contents
    ) {}

    public function run(Runtime $runtime): void {
        file_put_contents($this->path, $this->contents);
    }
}
