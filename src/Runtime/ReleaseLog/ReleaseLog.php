<?php
namespace IsThereAnyDeal\Tools\Deby\Runtime\ReleaseLog;

use IsThereAnyDeal\Tools\Deby\Exceptions\CorruptedException;
use IsThereAnyDeal\Tools\Deby\Runtime\Consts;
use IsThereAnyDeal\Tools\Deby\Ssh\SshClient;
use JsonSerializable;
use Traversable;

/**
 * @implements \IteratorAggregate<string, EStatus>
 */
class ReleaseLog implements JsonSerializable, \Countable, \IteratorAggregate
{
    private readonly SshClient $ssh;
    private readonly string $path;

    private ?string $current;

    /** @var array<string, EStatus> */
    private array $releases;

    public function __construct(SshClient $ssh) {
        $this->ssh = $ssh;
        $this->path = Consts::StatusDir."/".Consts::ReleasesLog;

        $this->current = null;
        $this->releases = [];
        $this->load();
    }

    private function load(): void {
        if (!$this->ssh->fileExists($this->path)) {
            return;
        }

        $json = $this->ssh->readFile($this->path);
        $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new CorruptedException();
        }

        if (!isset($data['releases']) || !is_array($data['releases'])) {
            throw new CorruptedException();
        }
        foreach($data['releases'] as $name => $status) {
            if (!is_string($status)) {
                throw new CorruptedException();
            }

            $estatus = EStatus::tryFrom($status);
            if (is_null($estatus)) {
                throw new CorruptedException();
            }

            $this->releases[$name] = $estatus;

            if ($estatus === EStatus::Current) {
                if (!is_null($this->current)) {
                    throw new CorruptedException();
                }

                $this->current = $name;
            }
        }
    }

    private function write(): void {
        $json = json_encode($this, flags: JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $this->ssh->writeFile($this->path, $json);
    }

    public function getCurrent(): ?string {
        return $this->current;
    }

    public function setStatus(string $name, EStatus $status): void {
        if (!is_null($this->current) && $status == EStatus::Current) {
            $this->releases[$this->current] = EStatus::Ready;
        }

        $this->releases[$name] = $status;
        $this->write();
    }

    public function delete(string $releaseName): void {
        unset($this->releases[$releaseName]);
        $this->write();
    }

    public function jsonSerialize(): mixed {
        return [
            "releases" => array_map(fn(EStatus $status) => $status->value, $this->releases)
        ];
    }

    public function count(): int {
        return count($this->releases);
    }

    /**
     * @return Traversable<string, EStatus>
     */
    public function getIterator(): Traversable {
        foreach($this->releases as $name => $status) {
            yield $name => $status;
        }
    }
}
