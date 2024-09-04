<?php
namespace IsThereAnyDeal\Tests\Deby\Runtime;

use IsThereAnyDeal\Tools\Deby\Runtime\ExecutionPlan;
use IsThereAnyDeal\Tools\Deby\Runtime\Setup;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;
use PHPUnit\Framework\TestCase;

class ExecutionPlanTest extends TestCase
{
    public function testSimple(): void {

        $setup = new Setup();
        $setup->recipe("simple-recipe")
            ->add("task1", $this->createStub(Task::class))
            ->add("task2", $this->createStub(Task::class))
            ->add("task3", $this->createStub(Task::class));

        $plan = new ExecutionPlan("simple-recipe", $setup, null);

        $iterator = $plan->getIterator();

        $step = $iterator->current();
        $this->assertTrue($step->execute);

        $tasks = $step->recipe->tasks();

        $task = $tasks->current();
        $this->assertEquals("task1", $task->name);
        $tasks->next();

        $task = $tasks->current();
        $this->assertEquals("task2", $task->name);
        $tasks->next();

        $task = $tasks->current();
        $this->assertEquals("task3", $task->name);
        $tasks->next();

        $task = $tasks->current();
        $this->assertNull($task);

        $iterator->next();
        $step = $iterator->current();
        $this->assertNull($step);
    }

    public function testDependencies(): void {

        $setup = new Setup();
        $setup->recipe("recipe:nested");
        $setup->recipe("recipe:nested:prod");

        $setup->recipe("recipe:dev", ["dev"])
            ->after("recipe:nested");

        $setup->recipe("recipe:prod", ["prod"])
            ->after("recipe:nested:prod")
            ->after("recipe:nested");

        $setup->recipe("recipe")
            ->after("recipe:dev")
            ->after("recipe:prod");

        /**
         * Plan for dev target should be: ([1/0] marks execute)
         * [1]recipe:nested, [1]recipe:dev, [0]recipe:nested:prod, [0]recipe:prod, [1]recipe
         */
        $plan = new ExecutionPlan("recipe", $setup, "dev");

        $iterator = $plan->getIterator();

        $step = $iterator->current();
        $this->assertEquals("recipe:nested", $step->recipe->name);
        $this->assertTrue($step->execute);
        $iterator->next();

        $step = $iterator->current();
        $this->assertEquals("recipe:dev", $step->recipe->name);
        $this->assertTrue($step->execute);
        $iterator->next();

        $step = $iterator->current();
        $this->assertEquals("recipe:nested:prod", $step->recipe->name);
        $this->assertFalse($step->execute);
        $iterator->next();

        $step = $iterator->current();
        $this->assertEquals("recipe:prod", $step->recipe->name);
        $this->assertFalse($step->execute);
        $iterator->next();

        $step = $iterator->current();
        $this->assertEquals("recipe", $step->recipe->name);
        $this->assertTrue($step->execute);
        $iterator->next();

        $this->assertNull($iterator->current());
    }
}