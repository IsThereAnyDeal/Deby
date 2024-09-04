<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime\Structs;

class ReleaseSetup
{
    public function __construct(
        public readonly string $name
    ) {
        if (empty($this->name)) {
            throw new \ErrorException("Release name may not be empty");
        }
    }
}