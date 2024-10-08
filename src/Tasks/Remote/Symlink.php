<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Runtime\Path;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

/**
 * Symlink shared files or folders
 */
#[Remote]
class Symlink implements Task
{
    /** @var list<string> */
    private readonly array $files;

    public function __construct(string ...$files) {
        $this->files = array_values($files);
    }

    public function run(Runtime $runtime): void {
        $ssh = $runtime->getActiveConnection()->getSshClient();
        $release = $runtime->getReleaseSetup();

        foreach($this->files as $file) {
            $ssh->symlink($release->path($file), Path::shared($file));
        }
    }
}
