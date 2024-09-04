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
        $setup->recipe("env-1-task", targets: ["env1"])
            ->add("Env 1 task", new class($ranTasks) implements Task {
                /** @param list<string> &$ranTasks */
                public function __construct(public array &$ranTasks) {}
                public function run(Runtime $runtime): void {
                    $this->ranTasks[] = "env-1";
                }
            });

        $setup->recipe("env-2-task", targets: ["env2"])
            ->add("Env 2 task", new class($ranTasks) implements Task {
                /** @param list<string> &$ranTasks */
                public function __construct(public array &$ranTasks) {}
                public function run(Runtime $runtime): void {
                    $this->ranTasks[] = "env-2";
                }
            });

        $setup->recipe("env-null-task", targets: [null])
            ->add("Env null task", new class($ranTasks) implements Task {
                /** @param list<string> &$ranTasks */
                public function __construct(public array &$ranTasks) {}
                public function run(Runtime $runtime): void {
                    $this->ranTasks[] = "env-null";
                }
            });

        $setup->recipe("env-any-task", targets: [])
            ->add("Env any task", new class($ranTasks) implements Task {
                /** @param list<string> &$ranTasks */
                public function __construct(public array &$ranTasks) {}
                public function run(Runtime $runtime): void {
                    $this->ranTasks[] = "env-any";
                }
            });

        $setup->recipe("recipe")
            ->after("env-1-task")
            ->after("env-2-task")
            ->after("env-null-task")
            ->after("env-any-task");

        ob_start();
        $runtime = new Runtime($setup);
        $runtime->printSkipped = true;

        $runtime->run("recipe", "env1");
        $this->assertEquals(["env-1", "env-any"], $ranTasks);
        $ranTasks = [];

        $runtime->run("recipe", "env2");
        $this->assertEquals(["env-2", "env-any"], $ranTasks);
        $ranTasks = [];

        $runtime->run("recipe", null);
        $this->assertEquals(["env-null", "env-any"], $ranTasks);
        $ranTasks = [];


        $runtime->printSkipped = false;

        $runtime->run("recipe", "env1");
        $this->assertEquals(["env-1", "env-any"], $ranTasks);
        $ranTasks = [];

        $runtime->run("recipe", "env2");
        $this->assertEquals(["env-2", "env-any"], $ranTasks);
        $ranTasks = [];

        $runtime->run("recipe", null);
        $this->assertEquals(["env-null", "env-any"], $ranTasks);
        $ranTasks = [];

        ob_end_clean();
    }
}