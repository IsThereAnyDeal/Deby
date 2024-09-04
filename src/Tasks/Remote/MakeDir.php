<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;
use IsThereAnyDeal\Tools\Deby\Tasks\Vars;

#[Remote]
class MakeDir implements Task
{
    public function __construct(
        private readonly string $dir,
        private readonly int $mode=0755
    ) {}

    public function run(Runtime $runtime): void {
        $ssh = $runtime->getSshClient();

        if (!$runtime->hasVar(Vars::ReleaseName)) {
            throw new \ErrorException("No release found");
        }

        $release = $runtime->getVar(Vars::ReleaseName);
        if (!is_string($release)) {
            throw new \InvalidArgumentException();
        }

        $ssh->mkdir("%releases%/{$release}/{$this->dir}", $this->mode);
    }
}
