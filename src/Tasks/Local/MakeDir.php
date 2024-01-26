<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Local;

use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

class MakeDir implements Task
{
    public function __construct(
        private readonly string $dir
    ) {}

    public function run(Runtime $runtime): void {
        mkdir($this->dir, recursive: true);
    }
}
