<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use DateTimeImmutable;
use IsThereAnyDeal\Tools\Deby\Runtime\ReleaseLog\EStatus;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;
use IsThereAnyDeal\Tools\Deby\Tasks\Vars;

class Push implements Task
{
    public function __construct(
        private readonly string $releaseArchivePath,
        private readonly ?string $releaseName=null
    ) {}

    public function run(Runtime $runtime): void {
        $ssh = $runtime->getSshClient();
        $releaseLog = $runtime->getReleaseLog();

        if (!$runtime->hasVar(Vars::ReleaseName)) {
            $releaseName = $this->releaseName ?? (new DateTimeImmutable())->format("Ymd-His");
            $runtime->setVar(Vars::ReleaseName, $releaseName);
        } else {
            $releaseName = $runtime->getVar(Vars::ReleaseName);
        }

        if (!is_string($releaseName)) {
            throw new \ErrorException("Invalid release name");
        }

        $releaseDir = "%releases%/$releaseName";
        $ssh->mkdir($releaseDir);

        $releaseLog->setStatus($releaseName, EStatus::New);

        $archive = $releaseDir."/archive.tar.gz";

        $ssh->upload($this->releaseArchivePath, $archive);
        $ssh->untar($archive);
        $ssh->remove($archive);
    }
}
