<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime\Structs;

use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

class TaskDescriptor {

    public readonly bool $remote;

    public function __construct(
        public readonly string $name,
        public readonly Task $task
    ) {
        $rc = new \ReflectionClass($task);
        $attributes = $rc->getAttributes(Remote::class);

        $this->remote = !empty($attributes);
    }
}
