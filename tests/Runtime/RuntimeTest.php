<?php
namespace IsThereAnyDeal\Tests\Deby\Runtime;

use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Runtime\Setup;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;
use PHPUnit\Framework\TestCase;

class RuntimeTest extends TestCase
{
    public function testAllowedTargets(): void {
        /** @var list<string> $ranTasks */
        $ranTasks = [];

        $setup = new Setup();
        $setup->local("env-1-task")
            ->add("Env 1 task", new class($ranTasks) implements Task {
                /** @param list<string> &$ranTasks */
                public function __construct(public array &$ranTasks) {}
                public function run(Runtime $runtime): void {
                    $this->ranTasks[] = "env-1";
                }
            });

        $setup->local("env-2-task")
            ->add("Env 2 task", new class($ranTasks) implements Task {
                /** @param list<string> &$ranTasks */
                public function __construct(public array &$ranTasks) {}
                public function run(Runtime $runtime): void {
                    $this->ranTasks[] = "env-2";
                }
            });

        $setup->local("env-null-task")
            ->add("Env null task", new class($ranTasks) implements Task {
                /** @param list<string> &$ranTasks */
                public function __construct(public array &$ranTasks) {}
                public function run(Runtime $runtime): void {
                    $this->ranTasks[] = "env-null";
                }
            });

        $setup->local("env-any-task")
            ->add("Env any task", new class($ranTasks) implements Task {
                /** @param list<string> &$ranTasks */
                public function __construct(public array &$ranTasks) {}
                public function run(Runtime $runtime): void {
                    $this->ranTasks[] = "env-any";
                }
            });

        $setup->local("recipe")
            ->after("env-1-task", allowTargets: ["env1"])
            ->after("env-2-task", allowTargets: ["env2"])
            ->after("env-null-task", allowTargets: [null])
            ->after("env-any-task", allowTargets: []);

        $runtime = new Runtime($setup);

        $runtime->run("recipe", "env1");
        $this->assertEquals(["env-1", "env-any"], $ranTasks);
        $ranTasks = [];

        $runtime->run("recipe", "env2");
        $this->assertEquals(["env-2", "env-any"], $ranTasks);
        $ranTasks = [];

        $runtime->run("recipe", null);
        $this->assertEquals(["env-null", "env-any"], $ranTasks);
        $ranTasks = [];
    }
}