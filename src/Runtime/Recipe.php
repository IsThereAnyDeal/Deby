<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime;

use Ds\Set;
use IsThereAnyDeal\Tools\Deby\Runtime\Structs\Dependency;
use IsThereAnyDeal\Tools\Deby\Runtime\Structs\TaskDescriptor;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;

class Recipe
{
    /** @var list<TaskDescriptor> */
    private array $tasks;

    /** @var list<Dependency> */
    private array $after;

    /** @var Set<string|null> */
    private readonly Set $allowedTargets;

    /**
     * @param list<string|null> $allowedTargets
     */
    public function __construct(
        public readonly string $name,
        array $allowedTargets
    ) {
        $this->allowedTargets = new Set($allowedTargets);
        $this->tasks = [];
        $this->after = [];
    }

    public function after(string $recipe): self {
        $this->after[] = new Dependency($recipe);
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

    public function hasRemoteTasks(): bool {
        foreach($this->tasks as $task) {
            if ($task->remote) {
                return true;
            }
        }
        return false;
    }

    public function allowTarget(?string $target): bool {
        return $this->allowedTargets->isEmpty()
            || $this->allowedTargets->contains($target);
    }

    public function add(string $name, Task $task, bool $include=true): self {
        if ($include) {
            $this->tasks[] = new TaskDescriptor($name, $task);
        }
        return $this;
    }

    /**
     * @return \Generator<TaskDescriptor>
     */
    public function tasks(): iterable {
        foreach($this->tasks as $task) {
            yield $task;
        }
    }
}
