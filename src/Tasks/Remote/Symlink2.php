<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

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
        $release = $runtime->getReleaseSetup()->name;

        foreach($this->files as $file) {
            $ssh->symlink("%releases%/{$release}/{$file}", "{$this->targetDir}/{$file}");
        }
    }
}
