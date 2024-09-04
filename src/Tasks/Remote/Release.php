<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Cli\Cli;
use IsThereAnyDeal\Tools\Deby\Cli\Color;
use IsThereAnyDeal\Tools\Deby\Cli\Style;
use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\ReleaseLog\EStatus;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Runtime\Structs\ReleaseSetup;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

#[Remote]
class Release implements Task
{
    public function __construct() {}

    public function run(Runtime $runtime): void {
        $conn = $runtime->getActiveConnection();
        $ssh = $conn->getSshClient();
        $releaseLog = $conn->getReleaseLog();

        if ($runtime->hasReleaseSetup()) {
            $release = $runtime->getReleaseSetup();
        } else {
            $isNewer = is_null($releaseLog->getCurrent());
            $release = null;
            foreach($releaseLog as $name => $status) {
                if ($isNewer && $status === EStatus::Ready) {
                    $release = new ReleaseSetup($name);
                } elseif($status === EStatus::Current) {
                    $isNewer = true;
                }
            }
            if (is_null($release)) {
                Cli::writeLn("No ready release found", Style::Faint, Color::Grey);
                return;
            }
        }

        $releaseDir = $release->dir();
        if (!$ssh->dirExists($releaseDir)) {
            throw new \ErrorException("Release {$release->name} not found");
        }

        Cli::writeLn("Release {$release->name}", Style::Faint, Color::Grey);
        $ssh->symlink("current", $releaseDir);
        $releaseLog->setStatus($release->name, EStatus::Current);
    }
}
