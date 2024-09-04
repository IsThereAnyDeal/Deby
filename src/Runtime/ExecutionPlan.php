<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime;

use Ds\Map;
use IsThereAnyDeal\Tools\Deby\Runtime\Structs\Step;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<Step>
 */
class ExecutionPlan implements IteratorAggregate
{
    /** @var list<Step> */
    private array $plan;

    public function __construct(
        string $recipeName,
        private readonly Setup $setup,
        private readonly ?string $target,
        private readonly bool $includeSkipped=true
    ) {
        $steps = $this->buildPlan($recipeName);

        /** @var Map<Recipe, bool> $recipes */
        $recipes = new Map();
        foreach($steps as $step) {
            $recipe = $step->recipe;
            $execute = $step->execute;

            if ($recipes->hasKey($recipe)) {
                $recipes->put($recipe, $recipes->get($recipe) || $execute);
            } else {
                $recipes->put($recipe, $execute);
            }
        }

        $this->plan = [];
        foreach($recipes as $recipe => $execute) {
            $this->plan[] = new Step($recipe, $execute);
        }
    }

    /**
     * @return list<Step>
     */
    private function buildPlan(string $recipeName, bool $execute=true): array {
        $recipe = $this->setup->getRecipe($recipeName);
        $plan = [];

        $executeThis = $execute && $recipe->allowTarget($this->target);
        if ($executeThis || $this->includeSkipped) {
            $dependencies = $recipe->getDependencies();
            foreach($dependencies as $dependency) {
                $plan = [...$plan, ...$this->buildPlan(
                    $dependency->name,
                    $executeThis
                )];
            }
            $plan[] = new Step($recipe, $executeThis);
        }

        return $plan;
    }

    public function hasRemoteTasks(): bool {
        foreach($this->plan as $step) {
            if ($step->execute && $step->recipe->hasRemoteTasks()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return \Iterator<Step>
     */
    public function getIterator(): \Iterator {
        return new \ArrayIterator($this->plan);
    }
}