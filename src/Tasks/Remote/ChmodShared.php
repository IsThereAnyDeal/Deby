<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;
use IsThereAnyDeal\Tools\Deby\Runtime\Path;

#[Remote]
class ChmodShared extends Chmod
{
    /**
     * @param list<string> $dirs
     */
    public function __construct(
        array $dirs,
        int $mode
    ) {
        parent::__construct(
            array_map(fn(string $dir) => Path::shared($dir), $dirs),
            $mode
        );
    }
}
