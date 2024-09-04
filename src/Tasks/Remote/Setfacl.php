<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;
use IsThereAnyDeal\Tools\Deby\Tasks\Vars;

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

        if (!$runtime->hasVar(Vars::ReleaseName)) {
            throw new \ErrorException("No release found");
        }

        $release = $runtime->getVar(Vars::ReleaseName);
        if (!is_string($release)) {
            throw new \InvalidArgumentException();
        }

        foreach($this->facl as $path => $options) {
            $remotePath = $ssh->remotePath("%releases%/{$release}/{$path}");
            $ssh->exec("setfacl {$options} {$remotePath}");
        }
    }
}
