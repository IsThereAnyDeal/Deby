<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;
use IsThereAnyDeal\Tools\Deby\Tasks\Vars;

/**
 * Symlink shared files or folders
 */
#[Remote]
class Symlink2 implements Task
{
    /** @var list<string> */
    private readonly array $files;

    private readonly string $targetDir;

    public function __construct(string $targetDir, string ...$files) {
        $this->targetDir = $targetDir;
        $this->files = array_values($files);
    }

    public function run(Runtime $runtime): void {
        $ssh = $runtime->getSshClient();

        if (!$runtime->hasVar(Vars::ReleaseName)) {
            throw new \ErrorException("No release found");
        }

        $release = $runtime->getVar(Vars::ReleaseName);
        if (!is_string($release)) {
            throw new \InvalidArgumentException();
        }

        foreach($this->files as $file) {
            $ssh->symlink("%releases%/{$release}/{$file}", "{$this->targetDir}/{$file}");
        }
    }
}
