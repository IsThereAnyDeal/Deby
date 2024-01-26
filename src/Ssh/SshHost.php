<?php
namespace IsThereAnyDeal\Tools\Deby\Ssh;

class SshHost
{
    public function __construct(
        public readonly string  $name,
        public readonly string  $host,
        public readonly int     $port,
        public readonly SshAuth $auth,
        public readonly string  $workingDir
    ) {}
}
