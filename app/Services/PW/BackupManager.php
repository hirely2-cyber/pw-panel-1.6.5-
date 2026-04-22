<?php

namespace App\Services\PW;

use Illuminate\Support\Collection;

/**
 * Simple filesystem-based backup listing for PW server / MySQL dumps.
 * Looks inside the configured backup directory for *.tar.gz / *.sql / *.sql.gz.
 */
class BackupManager
{
    /**
     * Top-level PW server runtime targets when server_path points to /home.
     * This avoids archiving unrelated home content.
     *
     * @return array<int, string>
     */
    protected function defaultServerTargets(): array
    {
        return [
            'gacd',
            'gamed',
            'gamedbd',
            'gauthd',
            'gdeliveryd',
            'gfactiond',
            'glinkd',
            'logservice',
            'uniquenamed',
            'iweb_map.sh',
            'libskill.so',
            'libtask.so',
            'license.conf',
            'register.php',
            'start',
            'start.sh',
            'stop',
            'stop.sh',
        ];
    }

    /**
     * Resolve which entries should be included in a server-files archive.
     *
     * @return array{base:string, label:string, entries:array<int, string>}
     */
    protected function resolveServerArchiveScope(string $src): array
    {
        if ($src === '/home') {
            $entries = array_values(array_filter(
                $this->defaultServerTargets(),
                fn (string $entry): bool => file_exists("{$src}/{$entry}")
            ));

            return [
                'base' => $src,
                'label' => 'pwserver',
                'entries' => $entries,
            ];
        }

        return [
            'base' => dirname($src),
            'label' => basename($src) ?: 'pwserver',
            'entries' => [basename($src)],
        ];
    }

    /** @return Collection<int, array{name:string,path:string,size:int,mtime:int,kind:string}> */
    public function list(?string $dir = null): Collection
    {
        $dir = rtrim((string) ($dir ?: config('pw.backup_dir') ?: storage_path('app/pw-backups')), '/');

        if (! is_dir($dir) || ! is_readable($dir)) {
            return collect();
        }

        $files = [];
        $dh = @opendir($dir);
        if (! $dh) {
            return collect();
        }
        while (($f = readdir($dh)) !== false) {
            if ($f === '.' || $f === '..') continue;
            $full = "{$dir}/{$f}";
            if (! is_file($full)) continue;

            $kind = match (true) {
                str_ends_with($f, '.sql.gz') => 'sql',
                str_ends_with($f, '.sql')    => 'sql',
                str_ends_with($f, '.tar.gz') => 'server',
                str_ends_with($f, '.tgz')    => 'server',
                str_ends_with($f, '.zip')    => 'archive',
                default                       => null,
            };
            if (! $kind) continue;

            $files[] = [
                'name'  => $f,
                'path'  => $full,
                'size'  => (int) @filesize($full),
                'mtime' => (int) @filemtime($full),
                'kind'  => $kind,
            ];
        }
        closedir($dh);

        return collect($files)->sortByDesc('mtime')->values();
    }

    public function find(string $name, ?string $dir = null): ?array
    {
        // Prevent traversal
        if (str_contains($name, '/') || str_contains($name, '..')) {
            return null;
        }
        return $this->list($dir)->firstWhere('name', $name);
    }

    /**
     * Create a gzipped mysqldump of the pw_game connection.
     *
     * @return array{ok:bool, name?:string, path?:string, size?:int, message:string}
     */
    public function backup(?string $connection = null, ?string $dir = null): array
    {
        $connection = $connection ?: 'pw_game';
        $cfg = config("database.connections.{$connection}");
        if (! $cfg || ($cfg['driver'] ?? '') !== 'mysql' && ($cfg['driver'] ?? '') !== 'mariadb') {
            return ['ok' => false, 'message' => "Unknown MySQL connection: {$connection}"];
        }

        $dir = rtrim((string) ($dir ?: config('pw.backup_dir') ?: storage_path('app/pw-backups')), '/');
        if (! is_dir($dir)) {
            if (! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
                return ['ok' => false, 'message' => "Cannot create backup directory: {$dir}"];
            }
        }
        if (! is_writable($dir)) {
            return ['ok' => false, 'message' => "Backup directory not writable: {$dir}"];
        }

        $mysqldump = trim((string) @shell_exec('command -v mysqldump')) ?: 'mysqldump';
        if (! @is_executable($mysqldump) && $mysqldump === 'mysqldump') {
            return ['ok' => false, 'message' => 'mysqldump binary not found on server'];
        }

        $name = sprintf('%s_%s.sql.gz', $cfg['database'], date('Ymd_His'));
        $path = "{$dir}/{$name}";

        // Use --defaults-file to avoid password on command line (CWE-214).
        $cnf = tempnam(sys_get_temp_dir(), 'pwmy_');
        @chmod($cnf, 0600);
        @file_put_contents($cnf, sprintf(
            "[client]\nhost=%s\nport=%s\nuser=%s\npassword=%s\n",
            $cfg['host'] ?? '127.0.0.1',
            $cfg['port'] ?? '3306',
            $cfg['username'] ?? 'root',
            $cfg['password'] ?? ''
        ));

        $db = escapeshellarg((string) ($cfg['database'] ?? ''));
        $cmd = sprintf(
            '%s --defaults-file=%s --single-transaction --quick --routines --triggers --events --default-character-set=utf8 %s 2>&1 | gzip -c > %s',
            escapeshellcmd($mysqldump),
            escapeshellarg($cnf),
            $db,
            escapeshellarg($path)
        );

        $output = [];
        $rc = 0;
        @exec($cmd, $output, $rc);
        @unlink($cnf);

        if ($rc !== 0 || ! is_file($path) || filesize($path) < 64) {
            @unlink($path);
            $msg = trim(implode("\n", $output)) ?: 'mysqldump failed';
            return ['ok' => false, 'message' => $msg];
        }

        return [
            'ok'      => true,
            'name'    => $name,
            'path'    => $path,
            'size'    => (int) filesize($path),
            'message' => "Backup created: {$name}",
        ];
    }

    /**
     * Create a gzipped tar archive of the PW server directory.
     * Excludes logs, core dumps, and the backup dir itself to keep size reasonable.
     *
     * @return array{ok:bool, name?:string, path?:string, size?:int, message:string}
     */
    public function backupServer(?string $serverPath = null, ?string $dir = null): array
    {
        $src = rtrim((string) ($serverPath ?: Server::current()->server_path ?? ''), '/');
        if ($src === '' || ! is_dir($src)) {
            return ['ok' => false, 'message' => "Server path not found: {$src}"];
        }

        $dir = rtrim((string) ($dir ?: config('pw.backup_dir') ?: storage_path('app/pw-backups')), '/');
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return ['ok' => false, 'message' => "Cannot create backup directory: {$dir}"];
        }
        if (! is_writable($dir)) {
            return ['ok' => false, 'message' => "Backup directory not writable: {$dir}"];
        }

        $tar = trim((string) @shell_exec('command -v tar')) ?: 'tar';
        if (! @is_executable($tar)) {
            return ['ok' => false, 'message' => 'tar binary not found on server'];
        }

        $scope = $this->resolveServerArchiveScope($src);
        if ($scope['entries'] === []) {
            return ['ok' => false, 'message' => "No server files found under: {$src}"];
        }

        $name = sprintf('%s_%s.tar.gz', $scope['label'], date('Ymd_His'));
        $path = "{$dir}/{$name}";

        // Default excludes: logs, core dumps, backup dir, temporary / cache files.
        $excludes = [
            'logs', '*/logs', '*/logs/*',
            '*.log', '*.core', 'core.*',
            basename($dir),
            'pw-backups', '*/pw-backups/*',
        ];
        $excludeArgs = '';
        foreach ($excludes as $e) {
            $excludeArgs .= ' --exclude='.escapeshellarg($e);
        }

        $entryArgs = implode(' ', array_map(
            static fn (string $entry): string => escapeshellarg($entry),
            $scope['entries']
        ));

        $cmd = sprintf(
            '%s -czf %s -C %s %s %s 2>&1',
            escapeshellcmd($tar),
            escapeshellarg($path),
            escapeshellarg($scope['base']),
            $excludeArgs,
            $entryArgs
        );

        $output = [];
        $rc = 0;
        @exec($cmd, $output, $rc);

        // tar can exit with 1 for "file changed as we read it" — treat as warning if archive exists and non-empty.
        if (! is_file($path) || filesize($path) < 1024) {
            @unlink($path);
            $msg = trim(implode("\n", $output)) ?: 'tar failed';
            return ['ok' => false, 'message' => $msg];
        }

        return [
            'ok'      => true,
            'name'    => $name,
            'path'    => $path,
            'size'    => (int) filesize($path),
            'message' => "Server archive created: {$name}".($rc !== 0 ? ' (with warnings)' : ''),
        ];
    }
}
