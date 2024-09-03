<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime;

class Dependency
{
    public function __construct(
        public readonly string $name
    ) {}
}
