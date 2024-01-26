<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks;

use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;

interface Task
{
    public function run(Runtime $runtime): void;
}
