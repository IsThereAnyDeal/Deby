<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;
use IsThereAnyDeal\Tools\Deby\Tasks\Vars;

#[Remote]
class Composer implements Task
{
    /**
     * @param list<string> $options
     */
    public function __construct(
        public readonly string $command,
        public readonly array $options=[]
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

        $releaseDir = $ssh->remotePath("%releases%/$release");
        $options = implode(" ", $this->options);
        $ssh->exec("composer {$this->command} {$options} -d {$releaseDir}");
    }
}
