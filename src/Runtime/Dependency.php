<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime;

use Ds\Set;

class Dependency
{
    public readonly string $name;

    /** @var Set<string> */
    private readonly Set $skipTargets;

    /**
     * @param list<string> $skipTargets
     */
    public function __construct(
        string $name,
        array $skipTargets
    ) {
        $this->name = $name;
        $this->skipTargets = new Set($skipTargets);
    }

    public function skipTarget(string $target): bool {
        return $this->skipTargets->contains($target);
    }
}
