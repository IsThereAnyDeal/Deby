<?php
namespace IsThereAnyDeal\Tools\Deby\Types;

use Ds\Map;

/**
 * @implements \IteratorAggregate<string, string>
 */
class AssetMap implements \IteratorAggregate
{
    /** @var Map<string, string> */
    private readonly Map $map;

    public function __construct(
        private readonly string $basePath
    ) {
        $this->map = new Map();
    }

    public function put(string $key, string $filepath): void {
        if ($this->map->hasKey($key)) {
            throw new \ErrorException("Asset map conflict: $key already exists");
        }

        $realBase = (string)realpath($this->basePath);
        $realFile = (string)realpath($filepath);

        $path = str_replace($realBase, "", $realFile)
            |> (fn(string $v) => str_replace("\\", "/", $v))
            |> (fn(string $v) => trim($v, "/"));

        $this->map->put($key, $path);
    }

    /**
     * @return \Traversable<string, string>
     */
    public function getIterator(): \Traversable {
        return $this->map->getIterator();
    }
}