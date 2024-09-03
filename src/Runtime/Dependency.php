<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime;

use Ds\Set;

class Dependency
{
    public readonly string $name;

    /** @var Set<string|null> */
    private readonly Set $allowedTargets;

    /**
     * @param list<string|null> $allowedTargets
     */
    public function __construct(
        string $name,
        array $allowedTargets
    ) {
        $this->name = $name;
        $this->allowedTargets = new Set($allowedTargets);
    }

    public function allowTarget(?string $target): bool {
        return $this->allowedTargets->isEmpty()
            || $this->allowedTargets->contains($target);
    }
}
