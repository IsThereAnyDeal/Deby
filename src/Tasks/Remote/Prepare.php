<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Runtime\Consts;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

class Prepare implements Task
{
    /**
     * @param list<string> $sharedDirs
     */
    public function __construct(
        private readonly array $sharedDirs
    ) {}

    public function run(Runtime $runtime): void {
        $ssh = $runtime->getSshClient();

        $dirs = [
            Consts::StatusDir,
            Consts::ReleasesDir,
            Consts::SharedDir,
            ...array_map(fn($dir) => Consts::SharedDir."/$dir", $this->sharedDirs)
        ];

        $ssh->mkdir2($dirs);
    }
}
