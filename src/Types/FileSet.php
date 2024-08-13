<?php
namespace IsThereAnyDeal\Tools\Deby\Types;

use ArrayIterator;
use Ds\Set;
use ErrorException;
use FilesystemIterator;
use GlobIterator;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<string>
 */
class FileSet implements IteratorAggregate
{
    private readonly string $dir;
    private string $dirPath;

    /** @var list<array{boolean, string}> */
    private array $patterns;

    public function __construct(string $dir) {
        $this->dir = $dir;
        $this->patterns = [];
    }

    private function dir(): string {
        if (!isset($this->dirPath)) {
            $dir = realpath($this->dir);
            if ($dir === false) {
                throw new ErrorException("Invalid path: '{$this->dir}'");
            }

            if (!is_dir($dir)) {
                throw new ErrorException("'$dir' is not a directory");
            }

            $this->dirPath = str_replace("\\", "/", $dir);
        }
        return $this->dirPath;
    }

    public function include(string $pattern): self {
        $this->patterns[] = [true, $this->makePattern($pattern)];
        return $this;
    }

    public function exclude(string $pattern): self {
        $this->patterns[] = [false, $this->makePattern($pattern)];
        return $this;
    }

    private function makePattern(string $pattern): string {
        $pattern = str_replace("\\", "/", $pattern);
        $pattern = trim($pattern, "/");
        $pattern = preg_replace("#/+#", "/", $pattern);
        if (is_null($pattern)) {
            throw new ErrorException();
        }

        $pattern = preg_replace("#^(\./|/)*#", "", $pattern);
        if (is_null($pattern)) {
            throw new ErrorException();
        }

        return $pattern;
    }

    /**
     * @param list<string> $rest
     * @param Set<string> $result
     */
    private function readdir(string $dir, string $pattern, array $rest, Set $result, bool $recursive): void {
        if ($pattern === "**") {
            if (count($rest) > 0) {
                $this->readdir($dir, $rest[0], array_slice($rest, 1), $result, true);
            }
            return;
        }

        if ($recursive) {
            $iterator = new FilesystemIterator($dir,
                FilesystemIterator::KEY_AS_FILENAME
                | FilesystemIterator::CURRENT_AS_PATHNAME
                | FilesystemIterator::UNIX_PATHS
                | FilesystemIterator::SKIP_DOTS
            );

            foreach($iterator as $filename => $path) {
                if (is_dir($path) && $filename[0] !== ".") {
                    $this->readdir($path, $pattern, $rest, $result, $recursive);
                }
            }
        }

        if (str_contains($pattern, "*")) {
            $iterator = new GlobIterator("{$dir}/{$pattern}",
                FilesystemIterator::KEY_AS_FILENAME
                | FilesystemIterator::CURRENT_AS_PATHNAME
                | FilesystemIterator::UNIX_PATHS
                | FilesystemIterator::SKIP_DOTS
            );

            foreach($iterator as $filename => $path) {
                if ($filename[0] === ".") { // ignore hidden folders
                    continue;
                }

                if (is_dir($path)) {
                    if ($pattern !== "*") {
                        $this->readdir($path, $rest[0] ?? "*", array_slice($rest, 1), $result, $recursive);
                    }
                } elseif (is_file($path)) {
                    $result->add($path);
                }
            }
        } else {
            $currentPath = $dir."/".$pattern;
            if (count($rest) == 0) {
                if (is_dir($currentPath)) {
                    $this->readdir($currentPath, "*", [], $result, true);
                } elseif (is_file($currentPath)) {
                    $result->add($currentPath);
                }
            } elseif (is_dir($currentPath)) {
                $this->readdir($currentPath, $rest[0], array_slice($rest, 1), $result, $recursive);
            }
        }
    }

    /**
     * @return list<string>
     */
    public function getFiles(): array {
        $result = new Set();
        $exclude = new Set();
        foreach($this->patterns as list($include, $pattern)) {
            $exclude->clear();

            $path = explode("/", $pattern);
            $this->readdir($this->dir(), $path[0], array_slice($path, 1), $include ? $result : $exclude, false);

            if (!$include) {
                $result = $result->diff($exclude);
            }
        }

        return $result->toArray();
    }

    public function getRelativePath(string $path): string {
        return substr($path, strlen($this->dir())+1);
    }

    public function getIterator(): Traversable {
        return new ArrayIterator($this->getFiles());
    }
}
