<?php
namespace IsThereAnyDeal\Tests\Deby\Types;

use IsThereAnyDeal\Tools\Deby\Types\FileSet;
use PHPUnit\Framework\TestCase;

class FileSetTest extends TestCase
{
    public function testSimplePattern(): void {

        $set = (new FileSet(__DIR__."/_data"))->include("js");
        $this->assertCount(4, $set->getFiles());

        $set = (new FileSet(__DIR__."/_data"))->include("js/*.js.map");
        $this->assertCount(2, $set->getFiles());
    }

}
