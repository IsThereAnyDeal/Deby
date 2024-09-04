<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime;

use IsThereAnyDeal\Tools\Deby\Runtime\ReleaseLog\ReleaseLog;
use IsThereAnyDeal\Tools\Deby\Ssh\SshClient;
use IsThereAnyDeal\Tools\Deby\Ssh\SshHost;

class Connection
{
    private ?string $hostName;
    private ?SshClient $sshClient;
    private ?ReleaseLog $releaseLog;

    public function __construct(SshHost $host) {
        $this->hostName = $host->name;

        $this->sshClient = new SshClient($host);
        $this->sshClient->connect();

        $this->releaseLog = new ReleaseLog($this->sshClient);
    }

    public function disconnect(): void {
        $this->sshClient?->disconnect();
        $this->releaseLog = null;
        $this->sshClient = null;
        $this->hostName = null;
    }

    public function getHostName(): ?string {
        return $this->hostName;
    }

    public function getSshClient(): ?SshClient {
        return $this->sshClient;
    }

    public function getReleaseLog(): ?ReleaseLog {
        return $this->releaseLog;
    }
}