<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\ReleaseLog\EStatus;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

#[Remote]
class Push implements Task
{
    public function __construct(
        private readonly string $releaseArchivePath
    ) {}

    public function run(Runtime $runtime): void {
        $ssh = $runtime->getSshClient();
        $releaseLog = $runtime->getReleaseLog();
        $releaseName = $runtime->getReleaseSetup()->name;

        $releaseDir = "%releases%/$releaseName";
        $ssh->mkdir($releaseDir);

        $releaseLog->setStatus($releaseName, EStatus::New);

        $archive = $releaseDir."/archive.tar.gz";

        $ssh->upload($this->releaseArchivePath, $archive);
        $ssh->untar($archive);
        $ssh->remove($archive);
    }
}
