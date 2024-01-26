<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Local;

use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

/**
 * Check whether paths exist
 */
class CheckPaths implements Task
{
    /** @var list<string> */
    private readonly array $paths;

    public function __construct(string ...$paths) {
        $this->paths = array_values($paths);
    }

    public function run(Runtime $runtime): void {
        foreach($this->paths as $path) {
            if (!file_exists($path)) {
                throw new \ErrorException("Path $path does not exist");
            }
        }
    }
}
