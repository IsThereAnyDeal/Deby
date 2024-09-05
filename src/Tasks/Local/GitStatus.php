<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Local;

use IsThereAnyDeal\Tools\Deby\Cli\Cli;
use IsThereAnyDeal\Tools\Deby\Cli\Color;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

class GitStatus implements Task
{
    public function __construct(
        private readonly bool $throwOnUncommited=false
    ) {}

    public function run(Runtime $runtime): void {
        $output = [];
        exec("git status --porcelain=2", $output, $resultCode);
        if ($resultCode !== 0) {
            throw new \ErrorException();
        }

        if (empty($output)) {
            return;
        }

        Cli::writeLn("There are uncommited changes in the working tree", Color::BrightRed);

        if ($this->throwOnUncommited) {
            throw new \ErrorException();
        }

        Cli::write("Do you want to continue? [Yn] ", Color::BrightRed);
        $input = strtolower(Cli::input());
        if (!in_array($input, ["y", "yes"])) {
            Cli::writeLn("Exiting", Color::Grey);
            die();
        }
    }
}