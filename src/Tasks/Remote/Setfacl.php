<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

#[Remote]
class Setfacl implements Task
{
    /**
     * @param array<string, string> $facl
     */
    public function __construct(
        private readonly array $facl
    ) {}

    #[\Override]
    public function run(Runtime $runtime): void {
        $ssh = $runtime->getSshClient();
        $release = $runtime->getReleaseSetup()->name;

        foreach($this->facl as $path => $options) {
            $remotePath = $ssh->remotePath("%releases%/{$release}/{$path}");
            $ssh->exec("setfacl {$options} {$remotePath}");
        }
    }
}
