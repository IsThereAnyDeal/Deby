<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

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
        $ssh = $runtime->getActiveConnection()->getSshClient();
        $release = $runtime->getReleaseSetup();

        $releaseDir = $ssh->path($release->dir());
        $options = implode(" ", $this->options);
        $ssh->exec("composer {$this->command} {$options} -d {$releaseDir}");
    }
}
