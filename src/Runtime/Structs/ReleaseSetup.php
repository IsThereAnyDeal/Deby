<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime\Structs;

use IsThereAnyDeal\Tools\Deby\Runtime\Path;

class ReleaseSetup
{
    public function __construct(
        public readonly string $name
    ) {
        if (empty($this->name)) {
            throw new \ErrorException("Release name may not be empty");
        }
    }

    public function dir(): Path {
        return Path::releases($this->name);
    }

    public function path(string $path): Path {
        return Path::releases("{$this->name}/{$path}");
    }
}