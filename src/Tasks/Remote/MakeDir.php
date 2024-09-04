<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

#[Remote]
class MakeDir implements Task
{
    public function __construct(
        private readonly string $dir,
        private readonly int $mode=0755
    ) {}

    public function run(Runtime $runtime): void {
        $ssh = $runtime->getActiveConnection()->getSshClient();
        $release = $runtime->getReleaseSetup();

        $ssh->mkdir($release->path($this->dir), $this->mode);
    }
}
