<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime;

use IsThereAnyDeal\Tools\Deby\Tasks\Task;

class TaskDescriptor {
    public function __construct(
        public readonly string $name,
        public readonly Task $task
    ) {}
}
