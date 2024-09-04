<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime\Structs;

use IsThereAnyDeal\Tools\Deby\Runtime\Recipe;

class Step
{
    public function __construct(
        public readonly Recipe $recipe,
        public readonly bool $execute
    ) {}
}
