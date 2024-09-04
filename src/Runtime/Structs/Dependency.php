<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime\Structs;

class Dependency
{
    public function __construct(
        public readonly string $name
    ) {}
}
