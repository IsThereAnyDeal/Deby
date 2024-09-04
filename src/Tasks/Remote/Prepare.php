<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\Consts;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Runtime\Path;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

#[Remote]
class Prepare implements Task
{
    /**
     * @param list<string> $sharedDirs
     */
    public function __construct(
        private readonly array $sharedDirs
    ) {}

    public function run(Runtime $runtime): void {
        $ssh = $runtime->getActiveConnection()->getSshClient();

        $dirs = [
            Consts::StatusDir,
            Consts::ReleasesDir,
            Consts::SharedDir,
            ...array_map(fn($dir) => Path::shared($dir), $this->sharedDirs)
        ];

        $ssh->mkdir2($dirs);
    }
}
