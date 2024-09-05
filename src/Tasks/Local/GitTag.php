<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Local;

use IsThereAnyDeal\Tools\Deby\Cli\Cli;
use IsThereAnyDeal\Tools\Deby\Cli\Color;
use IsThereAnyDeal\Tools\Deby\Cli\Style;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

class GitTag implements Task
{
    public function __construct(
        private readonly bool $push=true,
        private readonly string $prefix="build."
    ) {}

    public function run(Runtime $runtime): void {
        $release = $runtime->getReleaseSetup();

        $tag = "{$this->prefix}{$release->name}";

        $output = [];
        exec("git tag {$tag}", $output, $resultCode);
        if ($resultCode !== 0) {
            throw new \ErrorException();
        }
        Cli::writeln("Created {$tag} tag", Style::Faint, Color::Grey);

        if ($this->push) {
            $output = [];
            exec("git push --quiet origin tag {$tag}", $output, $resultCode);
            if ($resultCode !== 0) {
                throw new \ErrorException();
            }
            Cli::writeln("Pushed to origin", Style::Faint, Color::Grey);
        }
    }
}