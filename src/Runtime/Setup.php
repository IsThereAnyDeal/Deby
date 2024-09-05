<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime;

use ErrorException;
use IsThereAnyDeal\Tools\Deby\Ssh\SshAuth;
use IsThereAnyDeal\Tools\Deby\Ssh\SshHost;

/**
 * @phpstan-type Config array{
 *      auth: array<string, array{username: string, pubkey: string, privkey: string}>,
 *      hosts: array<string, array{host: string, port: int, auth: string, workingDir: string}>,
 *      targets: array<string, list<string>>
 *  }
 */
class Setup
{
    /** @var array<string, list<SshHost>> */
    private array $targets = [];

    /** @var array<string, Recipe> */
    private array $recipes = [];

    public function readTargetsConfig(string $path): void {
        if (!file_exists($path)) {
            throw new ErrorException("Targets config not found");
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new ErrorException("Could not read targets config");
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($extension === "yaml" || $extension === "yml") {
            if (!function_exists("yaml_parse_file")) {
                throw new ErrorException("Yaml extension not found");
            }

            /** @var Config $config */
            $config = yaml_parse($contents);
        } else {
            /** @var Config $config */
            $config = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        }

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
