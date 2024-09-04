<?php
namespace IsThereAnyDeal\Tools\Deby\Ssh;

use ErrorException;
use IsThereAnyDeal\Tools\Deby\Exceptions\NoConnectionException;

class SshClient
{
    /** @var ?resource $ssh */
    private $ssh = null;

    /** @var ?resource $sftp */
    private $sftp = null;

    private string $baseDir;

    public function __construct(
        private readonly SshHost $host
    ) {}

    public function connect(): void {
        if (!is_null($this->ssh)) {
            throw new ErrorException("Already connected");
        }

        $ssh = ssh2_connect(
            $this->host->host,
            $this->host->port,
            methods: [],
            callbacks: [
                "disconnect" => [$this, "disconnect"]
            ]
        );

        if ($ssh === false) {
            throw new ErrorException("Failed to connect to ssh");
        }

        if (!ssh2_auth_pubkey_file($ssh,
                $this->host->auth->username,
                $this->host->auth->pubkeyFile,
                $this->host->auth->privkeyFile
        )) {
            throw new ErrorException("SSH authentication failed");
        }

        $sftp = ssh2_sftp($ssh);
        if ($sftp === false) {
            throw new ErrorException("Failed to create sftp");
        }

        $this->ssh = $ssh;
        $this->sftp = $sftp;

        $realpath = ssh2_sftp_realpath($sftp, $this->host->workingDir);
        if ($realpath === false) {
            throw new ErrorException("Failed to set up base dir path");
        }
        $this->baseDir = $realpath;
    }

    public function exec(string $command, string &$stdout="", string &$stderr="", bool $throwOnError=true): bool {
        if (is_null($this->ssh)) {
            throw new ErrorException("Not connected");
        }

        $outStream = ssh2_exec($this->ssh, $command);
        if ($outStream === false) {
            throw new ErrorException("Couldn't get stdout stream");
        }

        $errStream = ssh2_fetch_stream($outStream,SSH2_STREAM_STDERR);
        if ($errStream === false) {
            throw new ErrorException("Couldn't get stderr stream");
        }

        stream_set_blocking($outStream, true);
        stream_set_blocking($errStream, true);

        $out = stream_get_contents($outStream);
        $err = stream_get_contents($errStream);

        /** @var array{exit_status?: integer} $metadata */
        $metadata = stream_get_meta_data($outStream); // @phpstan-ignore-line

        fclose($outStream);
        fclose($errStream);

        $exitCode = $metadata['exit_status'] ?? null;

        if ($out === false || $err === false) {
            throw new ErrorException("Failed to read stream");
        }

        $stdout = trim($out);
        $stderr = trim($err);

        if ($throwOnError && $exitCode !== 0) {
            throw new ErrorException($stderr);
        }

        return $exitCode === 0;
    }

    public function path(string $path): string {
        return "{$this->baseDir}/{$path}";
    }

    public function mkdir(string $dir, int $mode=0755): void {
        $this->mkdir2([$dir], $mode);
    }

    /**
     * @param list<string> $dirs
     */
    public function mkdir2(array $dirs, int $mode=0755): void {
        $dirs = array_map(fn(string $dir) => $this->path($dir), $dirs);
        $mode = base_convert((string)$mode, 10, 8);
        $this->exec("mkdir -p -m{$mode} ".implode(" ", $dirs));
    }

    public function rmdir(string $dir, bool $recursive=true): bool {
        if (is_null($this->sftp)) {
            throw new NoConnectionException();
        }

        $dir = $this->path($dir);
        if ($recursive) {
            $this->exec("rm -r $dir");
            return true;
        } else {
            return ssh2_sftp_rmdir($this->sftp, $dir);
        }
    }

    public function upload(string $localFile, string $remoteFile, int $mode=0644): bool {
        if (is_null($this->ssh)) {
            throw new NoConnectionException();
        }

        $localFile = realpath($localFile);
        if ($localFile === false || !is_file($localFile)) {
            throw new ErrorException("Could not find local file");
        }

        $remoteFile = $this->path($remoteFile);

        $send = ssh2_scp_send($this->ssh, $localFile, $remoteFile, $mode);
        ssh2_exec($this->ssh, "exit"); // flush buffers
        return $send;
    }

    public function untar(string $file): void {
        $path = $this->path($file);
        $dir = dirname($path);
        $name = basename($path);

        $this->exec("cd $dir; tar -xzf $name");
    }

    public function remove(string $path): bool {
        if (is_null($this->sftp)) {
            throw new NoConnectionException();
        }

        return ssh2_sftp_unlink($this->sftp, $this->path($path));
    }

    public function symlink(string $source, string $target, bool $replace=true): bool {
        if (is_null($this->sftp)) {
            throw new NoConnectionException();
        }

        if ($replace) {
            ssh2_sftp_unlink($this->sftp, $this->path($source));
        }

        return ssh2_sftp_symlink($this->sftp,
            $this->path($target),
            $this->path($source)
        );
    }

    public function dirExists(string $path): bool {
        $path = $this->path($path);
        return $this->exec("[[ -d $path ]]", throwOnError: false);
    }

    public function fileExists(string $path): bool {
        $path = $this->path($path);
        return $this->exec("[[ -f $path ]]", throwOnError: false);
    }

    public function readFile(string $path): string {
        $path = $this->path($path);

        $stream = fopen("ssh2.sftp://".intval($this->sftp).$path, "r");
        if ($stream === false) {
            throw new ErrorException("Could not open file $path");
        }

        $data = stream_get_contents($stream);
        if ($data === false) {
            throw new ErrorException("Failed to read file");
        }

        fclose($stream);
        return $data;
    }

    public function writeFile(string $path, string $data): void {
        $path = $this->path($path);

        $stream = fopen("ssh2.sftp://".intval($this->sftp).$path, "w");
        if ($stream === false) {
            throw new ErrorException("Could not open file $path");
        }

        $result = fwrite($stream, $data);
        if ($result === false) {
            throw new ErrorException("Failed to write file");
        }

        fclose($stream);
    }

    public function disconnect(): void {
        if (is_null($this->ssh)) {
            return;
        }
        ssh2_disconnect($this->ssh);
        $this->ssh = null;
        $this->sftp = null;
    }
}
