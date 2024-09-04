<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

#[Remote]
class Chmod implements Task
{
    /**
     * @param list<string> $dirs
     */
    public function __construct(
        private readonly array $dirs,
        private readonly int $mode
    ) {}

    public function run(Runtime $runtime): void {
        $ssh = $runtime->getSshClient();

        $mode = base_convert((string)$this->mode, 10, 8);
        $dirs = implode(" ", array_map(fn(string $dir) => $ssh->remotePath($dir), $this->dirs));
        $ssh->exec("chmod {$mode} {$dirs}");
    }
}
