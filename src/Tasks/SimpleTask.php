<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks;

use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;

class SimpleTask
{
    /**
     * @param callable(Runtime $runtime): void $task
     */
    public static function create(callable $task): Task {
        return new readonly class($task) implements Task {

            /**
             * @param callable(Runtime $runtime): void $task
             */
            public function __construct(private mixed $task) {}

            public function run(Runtime $runtime): void {
                ($this->task)($runtime);
            }
        };
    }
}