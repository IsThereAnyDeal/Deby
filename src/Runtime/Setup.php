<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime;

use ErrorException;
use IsThereAnyDeal\Tools\Deby\Ssh\SshAuth;
use IsThereAnyDeal\Tools\Deby\Ssh\SshHost;

class Setup
{
    /** @var array<string, list<SshHost>> */
    private array $targets = [];

    /** @var array<string, Recipe> */
    private array $recipes = [];

    public function readTargetsConfig(string $path): void {
        /**
         * @var array{
         *     auth: array<string, array{username: string, pubkey: string, privkey: string}>,
         *     hosts: array<string, array{host: string, port: int, auth: string, workingDir: string}>,
         *     targets: array<string, list<string>>
         * } $config
         */
        $config = json_decode(file_get_contents($path), true); // @phpstan-ignore-line

        $authMap = [];
        foreach($config['auth'] as $name => $data) {
            $authMap[$name] = new SshAuth(
                $data['username'],
                $data['pubkey'],
                $data['privkey']
            );
        }

        $hostMap = [];
        foreach($config['hosts'] as $name => $data) {
            $hostMap[$name] = new SshHost(
                $name,
                $data['host'],
                $data['port'],
                $authMap[$data['auth']],
                $data['workingDir']
            );
        }

        foreach($config['targets'] as $name => $hostNames) {
            $hosts = [];
            foreach($hostNames as $hn) {
                if (!isset($hostMap[$hn])) {
                    throw new ErrorException("Unknown host $hn");
                }
                $hosts[] = $hostMap[$hn];
            }
            $this->target($name, $hosts);
        }
    }

    /**
     * @param list<SshHost> $hosts
     */
    public function target(string $name, array $hosts): self {
        $this->targets[$name] = $hosts;
        return $this;
    }

    /**
     * @param list<string|null> $targets
     */
    public function recipe(string $name, array $targets=[]): Recipe {
        $recipe = new Recipe($name, allowedTargets: $targets);
        $this->recipes[$name] = $recipe;
        return $recipe;
    }

    /**
     * @return list<SshHost>
     */
    public function getTarget(string $name): array {
        if (!isset($this->targets[$name])) {
            throw new \InvalidArgumentException("Unknown target '$name'");
        }
        return $this->targets[$name];
    }

    public function getRecipe(string $name): Recipe {
        if (!isset($this->recipes[$name])) {
            throw new \InvalidArgumentException("Unknown recipe '$name'");
        }
        return $this->recipes[$name];
    }
}
