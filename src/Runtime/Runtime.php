<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime;

use DateTime;
use ErrorException;
use IsThereAnyDeal\Tools\Deby\Cli\Cli;
use IsThereAnyDeal\Tools\Deby\Cli\Color;
use IsThereAnyDeal\Tools\Deby\Runtime\ReleaseLog\ReleaseLog;
use IsThereAnyDeal\Tools\Deby\Runtime\Structs\ReleaseSetup;
use IsThereAnyDeal\Tools\Deby\Ssh\SshClient;

class Runtime
{
    public bool $printSkipped = true;

    private ?Connection $activeConnection = null;
    private ?ReleaseSetup $releaseSetup = null;

    /** @var array<string, mixed> */
    private array $vars = [];

    public function __construct(
        private readonly Setup $setup
    ) {}

    public function getHostName(): string {
        $hostName =  $this->activeConnection?->getHostName();
        if (is_null($hostName)) {
            throw new ErrorException();
        }
        return $hostName;
    }

    public function getSshClient(): SshClient {
        $client = $this->activeConnection?->getSshClient();
        if (is_null($client)) {
            throw new ErrorException();
        }
        return $client;
    }

    public function getReleaseLog(): ReleaseLog {
        $releaseLog = $this->activeConnection?->getReleaseLog();
        if (is_null($releaseLog)) {
            throw new ErrorException();
        }
        return $releaseLog;
    }

    public function hasReleaseSetup(): bool {
        return !is_null($this->releaseSetup);
    }

    public function getReleaseSetup(): ReleaseSetup {
        if (is_null($this->releaseSetup)) {
            throw new \ErrorException("Release has not been set up");
        }
        return $this->releaseSetup;
    }

    public function setupRelease(string $releaseName): self {
        if (!is_null($this->releaseSetup)) {
            throw new ErrorException("A release has already been set up");
        }
        $this->releaseSetup = new ReleaseSetup($releaseName);
        return $this;
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
     * @param ?string $target
     */
    public function run(string $recipeName, ?string $target): void {
        $start = microtime(true);

        $plan = new ExecutionPlan($recipeName, $this->setup, $target, $this->printSkipped);

        $this->execute($plan, $target);

        $total = microtime(true) - $start;
        Cli::write("Finished: ");
        Cli::writeln((string)round($total, 5)."s", Color::Yellow);
        Cli::writeLn();
    }

    private function execute(ExecutionPlan $plan, ?string $target): void {
        /** @var list<Connection> $connections */
        $connections = [];

        if ($plan->hasRemoteTasks()) {
            if (empty($target)) {
                throw new \InvalidArgumentException("Execution plan contains remote tasks but no target was specified");
            }

            $hosts = $this->setup->getTarget($target);
            if (empty($hosts)) {
                throw new \InvalidArgumentException("No hosts found for target");
            }

            foreach($hosts as $host) {
                $connections[] = new Connection($host);
            }
        }

        foreach($plan as $step) {
            $recipe = $step->recipe;
            $execute = $step->execute;

            if (!$recipe->hasTasks()) {
                continue;
            }

            Cli::writeLn("Recipe: {$recipe->name}", Color::Cyan);

            if (!$execute) {
                Cli::writeLn("Skipped", Color::Grey);
                Cli::writeLn();
                continue;
            }

            $start = microtime(true);

            foreach($recipe->tasks() as $task) {
                $time = (new DateTime())->format("H:i:s.v");
                Cli::write("{$time} ", Color::Grey);

                if ($task->remote) {
                    foreach($connections as $connection) {
                        $this->activeConnection = $connection;
                        $host = $this->getHostName();
                        Cli::write("[{$target}@{$host}] ", Color::Red);
                        Cli::writeLn($task->name, Color::Green);

                        $task->task->run($this);
                        $this->activeConnection = null;
                    }
                } else {
                    Cli::write("[local] ", Color::Blue);
                    Cli::writeLn($task->name, Color::Green);

                    $task->task->run($this);
                }
            }

            $total = microtime(true) - $start;

            Cli::write("Total: ");
            Cli::writeln((string)round($total, 5)."s", Color::Yellow);
            Cli::writeLn();
        }

        foreach($connections as $connection) {
            $connection->disconnect();
        }
    }
}
