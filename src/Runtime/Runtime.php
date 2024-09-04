<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime;

use DateTime;
use Ds\Map;
use ErrorException;
use IsThereAnyDeal\Tools\Deby\Cli\Cli;
use IsThereAnyDeal\Tools\Deby\Cli\Color;
use IsThereAnyDeal\Tools\Deby\Runtime\ReleaseLog\ReleaseLog;
use IsThereAnyDeal\Tools\Deby\Runtime\Structs\Step;
use IsThereAnyDeal\Tools\Deby\Ssh\SshClient;
use IsThereAnyDeal\Tools\Deby\Ssh\SshHost;

class Runtime
{
    private ?string $hostName = null;
    private ?SshClient $sshClient = null;
    private ?ReleaseLog $releaseLog = null;

    public bool $dontPrintSkipped = false;

    /** @var array<string, mixed> */
    private array $vars = [];

    public function __construct(
        private readonly Setup $setup
    ) {}

    public function getHostName(): string {
        if (is_null($this->hostName)) {
            throw new ErrorException();
        }
        return $this->hostName;
    }

    public function getSshClient(): SshClient {
        if (is_null($this->sshClient)) {
            throw new ErrorException();
        }
        return $this->sshClient;
    }

    public function getReleaseLog(): ReleaseLog {
        if (is_null($this->releaseLog)) {
            throw new ErrorException();
        }
        return $this->releaseLog;
    }

    public function hasVar(string $name): bool {
        return array_key_exists($name, $this->vars);
    }

    public function getVar(string $name): mixed {
        return $this->vars[$name];
    }

    public function setVar(string $name, mixed $value): void {
        $this->vars[$name] = $value;
    }

    /**
     * @param string $target
     */
    public function run(string $recipeName, ?string $target): void {
        $start = microtime(true);

        $dependencies = $this->buildPlan($recipeName, $target, false);

        /** @var Map<Recipe, bool> $plan */
        $plan = new Map();
        foreach($dependencies as $step) {
            $recipe = $step->recipe;
            $skipped = $step->skip;

            if ($plan->hasKey($recipe)) {
                $plan->put($recipe, $plan->get($recipe) && $skipped);
            } else {
                $plan->put($recipe, $skipped);
            }
        }

        $this->execute($plan, $target);

        $total = microtime(true) - $start;
        Cli::write("Finished: ");
        Cli::writeln((string)round($total, 5)."s", Color::Yellow);
        Cli::writeLn();
    }

    /**
     * @return list<Step>
     */
    private function buildPlan(string $recipeName, ?string $target, bool $skip): array {
        $recipe = $this->setup->getRecipe($recipeName);

        $skipRecipe = $skip || !$recipe->allowTarget($target);
        if ($skipRecipe && $this->dontPrintSkipped) {
            return [];
        }

        $plan = [];
        $dependencies = $recipe->getDependencies();
        foreach($dependencies as $dependency) {
            $plan = [...$plan, ...$this->buildPlan(
                $dependency->name,
                $target,
                $skipRecipe
            )];
        }
        $plan[] = new Step($recipe, $skipRecipe);

        return $plan;
    }

    private function connectHost(SshHost $host): void {
        $this->hostName = $host->name;

        $this->sshClient = new SshClient($host);
        $this->sshClient->connect();

        $this->releaseLog = new ReleaseLog($this->sshClient);
    }

    private function disconnectHost(): void {
        $this->sshClient?->disconnect();
        $this->releaseLog = null;
        $this->sshClient = null;
        $this->hostName = null;
    }

    /**
     * @param Map<Recipe, bool> $plan
     */
    private function execute(Map $plan, ?string $target): void {
        foreach($plan as $recipe => $skip) {
            if (!$recipe->hasTasks()) {
                continue;
            }

            Cli::writeLn("Recipe: {$recipe->name} [".($recipe->remote ? "remote" : "local")."]", Color::Cyan);

            if ($skip) {
                Cli::writeLn("Skipped", Color::Grey);
                Cli::writeLn();
                continue;
            }

            if ($recipe->remote) {
                if (empty($target)) {
                    throw new \InvalidArgumentException("Remote recipe requires target");
                }

                $hosts = $this->setup->getTarget($target);
                if (empty($hosts)) {
                    throw new \InvalidArgumentException("No hosts found for target");
                }

                foreach($hosts as $host) {
                    Cli::writeLn("Target: {$target}@{$host->name}", Color::Blue);
                    $this->connectHost($host);

                    $this->executeRecipe($recipe);

                    $this->disconnectHost();
                }
            } else {
                $this->executeRecipe($recipe);
            }
        }
    }

    private function executeRecipe(Recipe $recipe): void {
        $start = microtime(true);

        foreach($recipe->tasks() as $task) {
            $time = (new DateTime())->format("H:i:s.v");
            Cli::write($time."\t", Color::Green);
            Cli::writeLn($task->name, Color::Green);
            $task->task->run($this);
        }

        $total = microtime(true) - $start;

        Cli::write("Total: ");
        Cli::writeln((string)round($total, 5)."s", Color::Yellow);
        Cli::writeLn();
    }
}
