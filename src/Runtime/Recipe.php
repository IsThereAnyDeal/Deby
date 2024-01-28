<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime;

use DateTime;
use IsThereAnyDeal\Tools\Deby\Cli\Cli;
use IsThereAnyDeal\Tools\Deby\Cli\Color;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

class Recipe
{
    /** @var list<TaskDescriptor> */
    private array $tasks;

    /** @var list<Dependency> */
    private array $after;

    public function __construct(
        public readonly string $name,
        public readonly bool $remote
    ) {
        $this->tasks = [];
        $this->after = [];
    }

    /**
     * @param list<string> $skipTargets
     */
    public function after(string $recipe, array $skipTargets=[]): self {
        $this->after[] = new Dependency($recipe, $skipTargets);
        return $this;
    }

    /**
     * @return list<Dependency>
     */
    public function getDependencies(): array {
        return $this->after;
    }

    public function hasTasks(): bool {
        return !empty($this->tasks);
    }

    public function add(string $name, Task $task, bool $include=true): self {
        if ($include) {
            $this->tasks[] = new TaskDescriptor($name, $task);
        }
        return $this;
    }

    public function execute(Runtime $runtime): void {
        $start = microtime(true);

        foreach($this->tasks as $task) {
            $time = (new DateTime())->format("H:i:s.v");
            Cli::write($time."\t", Color::Green);
            Cli::writeLn($task->name, Color::Green);
            $task->task->run($runtime);
        }

        $total = microtime(true) - $start;

        Cli::write("Total: ");
        Cli::writeln((string)round($total, 5)."s", Color::Yellow);
        Cli::writeLn();
    }
}
