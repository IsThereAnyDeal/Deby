<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Cli\Cli;
use IsThereAnyDeal\Tools\Deby\Cli\Color;
use IsThereAnyDeal\Tools\Deby\Cli\Style;
use IsThereAnyDeal\Tools\Deby\Runtime\ReleaseLog\EStatus;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;
use IsThereAnyDeal\Tools\Deby\Tasks\Vars;

class Release implements Task
{
    public function __construct() {}

    public function run(Runtime $runtime): void {
        $ssh = $runtime->getSshClient();
        $releaseLog = $runtime->getReleaseLog();

        if ($runtime->hasVar(Vars::ReleaseName)) {
            $release = $runtime->getVar(Vars::ReleaseName);
            if (!is_string($release)) {
                throw new \InvalidArgumentException();
            }
        } else {
            $isNewer = is_null($releaseLog->getCurrent());
            $release = null;
            foreach($releaseLog as $name => $status) {
                if ($isNewer && $status === EStatus::Ready) {
                    $release = $name;
                } elseif($status === EStatus::Current) {
                    $isNewer = true;
                }
            }
            if (is_null($release)) {
                Cli::writeLn("No ready release found", Style::Faint, Color::Grey);
                return;
            }
        }

        $releaseDir = "%releases%/$release";
        if (!$ssh->dirExists($releaseDir)) {
            throw new \ErrorException("Release {$release} not found");
        }

        Cli::writeLn("Release {$release}", Style::Faint, Color::Grey);
        $ssh->symlink("current", $releaseDir);
        $releaseLog->setStatus($release, EStatus::Current);
    }
}
