<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use Ds\Set;
use IsThereAnyDeal\Tools\Deby\Cli\Cli;
use IsThereAnyDeal\Tools\Deby\Cli\Color;
use IsThereAnyDeal\Tools\Deby\Cli\Style;
use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

#[Remote]
class HostAwareUpload implements Task
{
    private string $currentFile;

    /** @var array<string, array<string, string>> */
    private array $map;

    /** @var Set<string> */
    private readonly Set $destinations;

    public function __construct() {
        $this->map = [];
        $this->destinations = new Set();
    }

    public function file(string $remoteFile): self {
        $this->currentFile = $remoteFile;
        $this->destinations->add($remoteFile);
        return $this;
    }

    public function map(string $host, string $name): self {
        $this->map[$this->currentFile][$host] = $name;
        return $this;
    }

    public function run(Runtime $runtime): void {
        $ssh = $runtime->getSshClient();
        $host = $runtime->getHostName();

        foreach($this->destinations as $remote) {
            if (!isset($this->map[$remote][$host])) {
                throw new \ErrorException("Missing {$remote} mapping for {$host}");
            }

            $local = $this->map[$remote][$host];
            Cli::writeln("Uploading ".basename($local)." as ".basename($remote), Style::Faint, Color::Grey);

            $ssh->upload($local, $remote);
        }
    }
}
