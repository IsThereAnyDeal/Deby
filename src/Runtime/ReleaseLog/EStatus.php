<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime\ReleaseLog;

enum EStatus: string
{
    case New = "new";
    case Ready = "ready";
    case Current = "current";
}
