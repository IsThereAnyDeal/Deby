<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Cli\Cli;
use IsThereAnyDeal\Tools\Deby\Cli\Color;
use IsThereAnyDeal\Tools\Deby\Cli\Style;
use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\ReleaseLog\EStatus;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

/**
 * Mark release as ready
 */
#[Remote]
class Ready implements Task
{
    public function __construct() {}

    public function run(Runtime $runtime): void {
        $releaseLog = $runtime->getActiveConnection()->getReleaseLog();
        $release = $runtime->getReleaseSetup();

        $releaseLog->setStatus($release->name, EStatus::Ready);
        Cli::writeLn("Release {$release->name} ready", Style::Faint, Color::Grey);
    }
}
