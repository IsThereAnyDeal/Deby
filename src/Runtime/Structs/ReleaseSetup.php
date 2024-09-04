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

    public function dir(): string {
        return "%releases%/{$this->name}";
    }

    public function path(string $path): string {
        return "%releases%/{$this->name}/{$path}";
    }
}