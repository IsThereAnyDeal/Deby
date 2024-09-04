<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Remote;

use IsThereAnyDeal\Tools\Deby\Runtime\Attributes\Remote;

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
            array_map(fn(string $dir) => "%shared%/{$dir}", $dirs),
            $mode
        );
    }
}
