<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime;

class Path implements \Stringable
{
    public static function shared(string $path=""): self {
        return new self(Consts::SharedDir."/{$path}");
    }

    public static function releases(string $path=""): self {
        return new self(Consts::ReleasesDir."/{$path}");
    }

    public static function current(string $path=""): self {
        return new self(Consts::CurrentDir."/{$path}");
    }

    private readonly string $path;

    private function __construct(string $path) {
        $this->path = rtrim($path, "/");
    }

    public function __toString(): string {
        return $this->path;
    }
}