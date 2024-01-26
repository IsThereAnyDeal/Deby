<?php
namespace IsThereAnyDeal\Tools\Deby\Ssh;

class SshAuth
{
    public readonly string $username;
    public readonly string $pubkeyFile;
    public readonly string $privkeyFile;

    public function __construct(
        string $username,
        string $pubkeyFile,
        string $privkeyFile
    ) {
        $pubkeyFile = realpath($pubkeyFile);
        if ($pubkeyFile === false || !is_file($pubkeyFile)) {
            throw new \InvalidArgumentException("Did not find pubkey file");
        }

        $privkeyFile = realpath($privkeyFile);
        if ($privkeyFile === false || !is_file($privkeyFile)) {
            throw new \InvalidArgumentException("Did not find privkey file");
        }

        $this->username = $username;
        $this->pubkeyFile = $pubkeyFile;
        $this->privkeyFile = $privkeyFile;
    }
}
