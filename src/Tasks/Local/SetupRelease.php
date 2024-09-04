<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Local;

use DateTimeImmutable;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

class SetupRelease implements Task
{
    public function __construct(
        private readonly ?string $releaseName=null
    ) {}

    public function run(Runtime $runtime): void {
        $releaseName = $this->releaseName ?? (new DateTimeImmutable())->format("Ymd-His");
        $runtime->setupRelease($releaseName);
    }
}
