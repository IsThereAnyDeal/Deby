<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

#[Remote]
class Chmod implements Task
{
    /**
     * @param list<string> $paths
     */
    public function __construct(
        private readonly array $paths,
        private readonly int   $mode
    ) {}

    public function run(Runtime $runtime): void {
        $ssh = $runtime->getActiveConnection()->getSshClient();

        $mode = base_convert((string)$this->mode, 10, 8);
        $dirs = implode(" ", array_map(fn(string $path) => $ssh->path($path), $this->paths));
        $ssh->exec("chmod {$mode} {$dirs}");
    }
}
