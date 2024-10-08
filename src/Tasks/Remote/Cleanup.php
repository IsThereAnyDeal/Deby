<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Cli\Cli;
use IsThereAnyDeal\Tools\Deby\Cli\Color;
use IsThereAnyDeal\Tools\Deby\Cli\Style;
use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\ReleaseLog\EStatus;
use IsThereAnyDeal\Tools\Deby\Runtime\ReleaseLog\ReleaseLog;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Runtime\Path;
use IsThereAnyDeal\Tools\Deby\Ssh\SshClient;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

/**
 * Cleanup old releases:
 *
 * - releases with New status - most likely failed to deploy and got stuck in New state
 * - releases that go beyond the limit of releases to keep
 */
#[Remote]
class Cleanup implements Task
{
    public function __construct(
        private readonly int $maxReleases=10
    ) {}

    private function removeRelease(SshClient $ssh, ReleaseLog $log, string $name, EStatus $status): void {
        $releaseDir = Path::releases($name);

        if ($ssh->dirExists($releaseDir)) {
            Cli::writeln("Remove release {$name} ({$status->value})", Style::Faint, Color::Grey);
            $ssh->rmdir($releaseDir);
        }
        $log->delete($name);
    }

    public function run(Runtime $runtime): void {
        $conn = $runtime->getActiveConnection();
        $ssh = $conn->getSshClient();
        $log = $conn->getReleaseLog();

        foreach($log as $name => $status) {
            if ($status === EStatus::New) {
                $this->removeRelease($ssh, $log, $name, $status);
            }
        }

        if (count($log) > $this->maxReleases) {
            $toDelete = count($log) - $this->maxReleases;
            foreach($log as $name => $status) {
                if ($status === EStatus::Current) {
                    continue;
                }

                $this->removeRelease($ssh, $log, $name, $status);

                if (--$toDelete == 0) {
                    break;
                }
            }
        }

        Cli::writeln("Releases: ".count($log), Style::Faint, Color::Grey);
    }
}
