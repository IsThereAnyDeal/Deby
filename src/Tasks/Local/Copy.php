<?php
namespace IsThereAnyDeal\Tools\Deby\Tasks\Local;

use Ds\Map;
use IsThereAnyDeal\Tools\Deby\Runtime\Runtime;
use IsThereAnyDeal\Tools\Deby\Tasks\Task;
use IsThereAnyDeal\Tools\Deby\Types\FileSet;

class Copy implements Task
{
    /**
     * @param ?Map<string, string> $assetMap
     */
    public function __construct(
        private readonly string $destination,
        private readonly FileSet $files,
        private readonly string $pattern="[dir]/[name].[ext]",
        private readonly ?Map $assetMap=null,
        private readonly ?string $mapPrefix=null,
        private readonly ?string $mapPattern=null
    ) {}

    /**
     * @param array<string, string> $replacements
     */
    private function processPattern(string $pattern, array $replacements): string {
        $target = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $pattern
        );
        $target = (string)preg_replace("#[\\/]+#", "/", $target);
        return rtrim(trim($target, "/"), ".");
    }

    public function run(Runtime $runtime): void {
        $destination = $this->destination;

        if (!file_exists($this->destination)) {
            mkdir($this->destination, recursive: true);
        }

        if (!is_dir($this->destination)) {
            throw new \ErrorException("{$this->destination} is not a directory");
        }

        $withHash = str_contains($this->pattern, "[hash]");
        $destination = str_replace("\\", "/", realpath($destination).DIRECTORY_SEPARATOR);
        foreach($this->files as $file) {
            $relPath = $this->files->getRelativePath($file);
            $basename = pathinfo($relPath, PATHINFO_BASENAME);
            $dirname = pathinfo($relPath, PATHINFO_DIRNAME)
                    |> (fn(string $dirname) => $dirname === "." ? "" : $dirname);

            $m = [];
            preg_match("#(?<filename>[^.]+)(?:\.(?<extension>.+$))?#", $basename, $m);
            $filename = $m['filename'] ?? null;
            $extension = $m['extension'] ?? "";

            if (empty($filename)) {
                throw new \ErrorException("Invalid file path");
            }

            $hash = "";
            if ($withHash) {
                $contents = file_get_contents($file);
                if ($contents === false) {
                    throw new \ErrorException("Failed to load file $file");
                }
                $hash = hash("xxh32", $contents);
            }

            $parts = [
                "[dir]" => $dirname,
                "[name]" => $filename,
                "[ext]" => $extension,
                "[hash]" => $hash
            ];
            $target = $this->processPattern($this->pattern, $parts);
            $fileDestination = $destination.$target;

            $targetDir = dirname($fileDestination);
            if (!file_exists($targetDir) || !is_dir($targetDir)) {
                mkdir($targetDir, recursive: true);
            }

            copy($file, $fileDestination);

            if (!is_null($this->assetMap)) {
                $key = is_null($this->mapPattern)
                    ? ($this->mapPrefix ?? "").$relPath
                    : $this->processPattern($this->mapPattern, $parts);

                if ($this->assetMap->hasKey($key)) {
                    throw new \ErrorException("Asset map conflict: $key already exists");
                }
                $this->assetMap->put($key, $target);
            }
        }
    }
}
