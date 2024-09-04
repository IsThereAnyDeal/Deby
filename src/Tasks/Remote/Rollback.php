<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Cli\Cli;
use IsThereAnyDeal\Tools\Deby\Cli\Color;
use IsThereAnyDeal\Tools\Deby\Cli\Style;
use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\ReleaseLog\EStatus;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Runtime\Path;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

#[Remote]
class Rollback implements Task
{
    public function __construct() {}

    public function run(Runtime $runtime): void {
        $conn = $runtime->getActiveConnection();
        $ssh = $conn->getSshClient();
        $releaseLog = $conn->getReleaseLog();

        $prev = null;
        foreach($releaseLog as $name => $status) {
            if ($status === EStatus::Ready) {
                $prev = $name;
            } elseif ($status === EStatus::Current) {
                break;
            }
        }

        if (is_null($prev)) {
            Cli::writeLn("No previous release found", Style::Faint, Color::Grey);
            return;
        }

        $releaseDir = Path::releases($prev);
        if (!$ssh->dirExists($releaseDir)) {
            throw new \ErrorException("Release {$prev} not found");
        }

        $ssh->symlink(Path::current(), $releaseDir);
        $releaseLog->setStatus($prev, EStatus::Current);

        Cli::writeLn("Rolled back to {$prev}", Style::Faint, Color::Grey);
    }
}
