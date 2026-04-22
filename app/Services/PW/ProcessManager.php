<?php

namespace App\Services\PW;

use Illuminate\Support\Facades\Log;

/**
 * Status dan kontrol service PW (gauthd, gdeliveryd, dll).
 */
final class ProcessManager
{
    public function __construct(private readonly Socket $socket) {}

    public static function forCurrent(): self
    {
        return new self(Socket::fromCurrent());
    }

    /**
     * @return array<string, array{status:string, online:bool, count:int, process:array}>
     */
    public function status(): array
    {
        $result = [];
        $services = config('pw.services');
        $authType = Server::current()->auth_type;

        foreach ($services as $key => $svc) {
            $program = $key === 'auth' ? $authType : $svc['program'];

            try {
                $raw = $this->socket->sendPacket(2, Socket::packString($program), 1024 * 100);
            } catch (PwSocketException $e) {
                Log::warning("PW status check failed for {$key}: " . $e->getMessage());
                $result[$key] = ['status' => 'error', 'online' => false, 'count' => 0, 'process' => []];
                continue;
            }

            if ($raw === 'off' || $raw === '') {
                $result[$key] = ['status' => 'offline', 'online' => false, 'count' => 0, 'process' => []];
                continue;
            }

            $lines = array_filter(explode("\n", trim($raw)));
            $processes = [];
            $rssKb = 0;
            $cpu   = 0.0;
            $memPct= 0.0;
            foreach ($lines as $line) {
                $cols = str_getcsv(preg_replace('/\s{2,}/', ' ', $line), ' ');
                $processes[] = $cols;
                // ps aux cols: 0=USER 1=PID 2=%CPU 3=%MEM 4=VSZ 5=RSS ...
                $cpu    += (float) ($cols[2] ?? 0);
                $memPct += (float) ($cols[3] ?? 0);
                $rssKb  += (int)   ($cols[5] ?? 0);
            }

            $result[$key] = [
                'status'  => 'online',
                'online'  => true,
                'count'   => count($processes),
                'process' => $processes,
                'cpu'     => round($cpu, 1),
                'mem_pct' => round($memPct, 1),
                'rss_mb'  => (int) round($rssKb / 1024),
            ];
        }

        return $result;
    }

    /**
     * Baca /proc/meminfo via daemon (opcode 57) → RAM/Swap stats (MB).
     */
    public function memory(): array
    {
        $raw = null;
        try {
            $raw = $this->socket->sendPacket(57, Socket::packString('/proc/meminfo'));
            if ($raw === 'File not found' || $raw === '') {
                $raw = null;
            }
        } catch (PwSocketException $e) {
            $raw = null;
        }

        if ($raw === null && is_readable('/proc/meminfo')) {
            $raw = (string) @file_get_contents('/proc/meminfo');
        }

        if (! $raw) {
            return [];
        }

        $data = [];
        foreach (explode("\n", $raw) as $line) {
            if (! str_contains($line, ':')) {
                continue;
            }
            [$k, $v] = explode(':', $line, 2);
            $data[trim($k)] = (int) (trim(str_replace(' kB', '', $v)) / 1024); // MB
        }
        return $data;
    }

    /**
     * Cek apakah link port listening = server "up".
     */
    public function isOnline(): bool
    {
        $server = Server::current();
        $fp = @fsockopen($server->socket_host, $server->link_port, $eno, $estr, 2);
        if ($fp) {
            fclose($fp);
            return true;
        }
        return false;
    }

    /**
     * Start all PW services via opcode 0, berurutan dengan delay.
     *
     * @param  callable|null  $onProgress  fn(string $key, string $phase, array $extra): void
     *                                     phase: 'start' | 'ok' | 'fail' | 'wait' | 'done'
     */
    public function startAll(?callable $onProgress = null): array
    {
        $server    = Server::current();
        $services  = config('pw.services');
        $startPlan = config('pw.start_order', []);
        $authType  = $server->auth_type;
        $basePath  = rtrim((string) $server->server_path, '/');
        $logsPath  = rtrim((string) $server->logs_path, '/');

        if (empty($startPlan)) {
            $startPlan = array_fill_keys(array_keys($services), 1);
        }

        $failed = []; $started = [];

        foreach ($startPlan as $key => $delay) {
            if (! isset($services[$key])) continue;
            $svc = $services[$key];
            $program   = $svc['program'];
            $dir       = $basePath . '/' . $svc['dir'];
            $configBase= $svc['config'];
            $instances = (int) ($svc['instances'] ?? 1);

            for ($i = 1; $i <= $instances; $i++) {
                // glinkd pakai argumen "gamesys.conf 1", "gamesys.conf 2", dst.
                // service lain pakai config apa adanya.
                $config  = ($instances > 1) ? "{$configBase} {$i}" : $configBase;
                $pidName = ($key === 'auth') ? $authType
                     : (($instances > 1) ? ($program . ' ' . $config) : ($program . ' ' . $config));
                $logSfx  = ($instances > 1) ? "_{$i}" : '';
                $cmd     = "cd {$dir}; ./{$program} {$config} > {$logsPath}/{$program}{$logSfx}_iweb.log &";

                $instKey = ($instances > 1) ? "{$key}#{$i}" : $key;
                if ($onProgress) {
                    $onProgress($instKey, 'start', ['program' => $program, 'parent' => $key]);
                    // Beri waktu SSE client me-render badge "Starting…" sebelum socket call cepat
                    usleep(350_000);
                }

                try {
                    $result = $this->socket->sendPacket(0,
                        Socket::packString($pidName) . Socket::packString($cmd));
                    if ($result === 'off') {
                        $failed[] = $instKey;
                        if ($onProgress) $onProgress($instKey, 'fail', ['reason' => 'daemon returned off', 'parent' => $key]);
                    } else {
                        $started[] = $instKey;
                        if ($onProgress) $onProgress($instKey, 'ok', ['parent' => $key]);
                    }
                } catch (PwSocketException $e) {
                    Log::warning("PW start failed for {$instKey}: " . $e->getMessage());
                    $failed[] = $instKey;
                    if ($onProgress) $onProgress($instKey, 'fail', ['reason' => $e->getMessage(), 'parent' => $key]);
                }

                // Jeda antar instance (pendek, 0.5 detik)
                if ($i < $instances) usleep(500_000);
            }

            if ($delay > 0) {
                if ($onProgress) $onProgress($key, 'wait', ['seconds' => (int) $delay]);
                sleep((int) $delay);
            }
        }

        if ($onProgress) $onProgress('', 'done', ['ok' => empty($failed), 'failed' => $failed]);
        return ['ok' => empty($failed), 'failed' => $failed, 'started' => $started];
    }

    /**
     * Stop all PW services via opcode 1 (+ opcode 9), berurutan dengan jeda.
     */
    public function stopAll(?callable $onProgress = null): array
    {
        $stopOrder = config('pw.stop_order', []);
        $services  = config('pw.services', []);
        $delay     = (int) config('pw.stop_delay', 1);
        $failed    = [];
        $authType  = Server::current()->auth_type;

        foreach ($stopOrder as $entry) {
            // Accept both service key ("auth") and program name ("gauthd") for
            // backward compatibility.
            if (isset($services[$entry])) {
                $key     = $entry;
                $program = $key === 'auth' ? $authType : ($services[$key]['program'] ?? $key);
            } else {
                // Fallback: treat as program name, reverse-lookup key.
                $program = $entry;
                $key = $entry;
                foreach ($services as $k => $svc) {
                    $prog = $k === 'auth' ? $authType : ($svc['program'] ?? $k);
                    if ($prog === $entry) { $key = $k; break; }
                }
            }

            if ($onProgress) {
                $onProgress($key, 'start', ['program' => $program]);
                usleep(250_000);
            }
            try {
                $result = $this->socket->sendPacket(1, Socket::packString($program));
                // NOTE: opcode 1 (stop) sering balas 'off' sebagai konfirmasi bahwa
                // service sekarang sudah off — itu SUKSES, bukan fail.
                // Fail hanya kalau exception / socket error.
                if ($onProgress) $onProgress($key, 'ok', ['program' => $program, 'result' => $result]);
            } catch (PwSocketException $e) {
                Log::warning("PW stop failed for {$program}: " . $e->getMessage());
                $failed[] = $program;
                if ($onProgress) $onProgress($key, 'fail', ['reason' => $e->getMessage()]);
            }
            if ($delay > 0) sleep($delay);
        }

        // opcode 9 = shutdown iweb daemon supervisor (hanya untuk stop-all)
        try { $this->socket->sendPacket(9, ''); } catch (PwSocketException) {}

        if ($onProgress) $onProgress('', 'done', ['ok' => empty($failed), 'failed' => $failed]);
        return ['ok' => empty($failed), 'failed' => $failed];
    }

    /**
     * Stop ONLY gs (all map instances).
     * Tidak menyentuh service lain (glinkd, gdeliveryd, auth, logservice, dll.).
     */
    public function stopGs(): array
    {
        $failed = [];
        try {
            // opcode 1 sering balas 'off' = konfirmasi sudah off → itu sukses.
            $this->socket->sendPacket(1, Socket::packString('gs'));
        } catch (PwSocketException $e) {
            Log::warning('PW stopGs failed: ' . $e->getMessage());
            $failed[] = 'gs';
        }
        // NOTE: opcode 9 sengaja TIDAK dipanggil di sini karena ia memicu
        // shutdown iweb daemon supervisor → service lain (logservice dll.)
        // ikut dibunuh. opcode 9 hanya dipakai di stopAll().

        return ['ok' => empty($failed), 'failed' => $failed];
    }

    /**
     * Start one or more map (gs location) instances.
     * Command per map:
     *   cd <gs_dir>; ./gs <mapId> gs.conf gmserver.conf gsalias.conf > logs/<mapId>_iweb.log &
     *
     * @param  string[]       $mapIds  e.g. ['is01','gs01']
     * @param  int            $delay   seconds to wait between each map
     * @param  callable|null  $onProgress fn(string $mapId, string $phase, array $extra)
     */
    public function startMaps(array $mapIds, int $delay = 0, ?callable $onProgress = null): array
    {
        $server   = Server::current();
        $gs       = config('pw.services.gs');
        $basePath = rtrim((string) $server->server_path, '/');
        $logsPath = rtrim((string) $server->logs_path, '/');
        $dir      = $basePath . '/' . ($gs['dir'] ?? 'gamed');
        $program  = $gs['program'] ?? 'gs';

        $failed = []; $started = [];

        foreach ($mapIds as $i => $mapId) {
            $mapId = trim((string) $mapId);
            if ($mapId === '') continue;

            $pidName = "{$program} {$mapId}";
            $cmd = "cd {$dir}; ./{$program} {$mapId} gs.conf gmserver.conf gsalias.conf > {$logsPath}/{$mapId}_iweb.log &";

            if ($onProgress) {
                $onProgress($mapId, 'start', []);
                usleep(250_000);
            }

            try {
                $result = $this->socket->sendPacket(0,
                    Socket::packString($pidName) . Socket::packString($cmd));
                if ($result === 'off') {
                    $failed[] = $mapId;
                    if ($onProgress) $onProgress($mapId, 'fail', ['reason' => 'daemon returned off']);
                } else {
                    $started[] = $mapId;
                    if ($onProgress) $onProgress($mapId, 'ok', []);
                }
            } catch (PwSocketException $e) {
                Log::warning("PW map start failed for {$mapId}: " . $e->getMessage());
                $failed[] = $mapId;
                if ($onProgress) $onProgress($mapId, 'fail', ['reason' => $e->getMessage()]);
            }

            if ($delay > 0 && $i < count($mapIds) - 1) sleep($delay);
        }

        if ($onProgress) $onProgress('', 'done', ['ok' => empty($failed), 'failed' => $failed]);
        return ['ok' => empty($failed), 'failed' => $failed, 'started' => $started];
    }

    /**
     * Stop running maps by killing their PIDs (opcode 7).
     * Looks up PIDs from current gs process list.
     *
     * @param  string[]       $mapIds
     * @param  int            $delay  seconds between kills
     * @param  callable|null  $onProgress
     */
    public function stopMaps(array $mapIds, int $delay = 0, ?callable $onProgress = null): array
    {
        // Build mapId → pid map from current `ps` of gs
        $running = [];
        try {
            $raw = $this->socket->sendPacket(2, Socket::packString('gs'), 1024 * 100);
            if ($raw !== 'off' && $raw !== '') {
                foreach (array_filter(explode("\n", trim($raw))) as $line) {
                    $cols = str_getcsv(preg_replace('/\s{2,}/', ' ', $line), ' ');
                    $pid   = (int) ($cols[1] ?? 0);
                    $argv1 = trim((string) ($cols[11] ?? ''));
                    if ($pid > 0 && $argv1 !== '') {
                        $running[$argv1] = $pid;
                    }
                }
            }
        } catch (PwSocketException $e) {
            Log::warning('PW ps(gs) failed: ' . $e->getMessage());
        }

        $failed = []; $killed = [];
        foreach ($mapIds as $i => $mapId) {
            $mapId = trim((string) $mapId);
            if ($mapId === '') continue;

            if ($onProgress) {
                $onProgress($mapId, 'start', []);
                usleep(200_000);
            }

            $pid = $running[$mapId] ?? 0;
            if ($pid <= 0) {
                $failed[] = $mapId;
                if ($onProgress) $onProgress($mapId, 'fail', ['reason' => 'not running']);
                continue;
            }

            try {
                $this->socket->sendPacket(7, Socket::packInt($pid));
                $killed[] = $mapId;
                if ($onProgress) $onProgress($mapId, 'ok', ['pid' => $pid]);
            } catch (PwSocketException $e) {
                Log::warning("PW kill failed for {$mapId} pid={$pid}: " . $e->getMessage());
                $failed[] = $mapId;
                if ($onProgress) $onProgress($mapId, 'fail', ['reason' => $e->getMessage()]);
            }

            if ($delay > 0 && $i < count($mapIds) - 1) sleep($delay);
        }

        if ($onProgress) $onProgress('', 'done', ['ok' => empty($failed), 'failed' => $failed]);
        return ['ok' => empty($failed), 'failed' => $failed, 'killed' => $killed];
    }

    /**
     * Drop OS caches (page/dentry/inode) to free RAM.
     *
     * @return array{ok:bool, method:string, freed_mb:int, before_mb:int, after_mb:int, message:string}
     */
    public function clearCache(): array
    {
        $before  = $this->memory()['MemAvailable'] ?? 0;
        $method  = 'none';
        $ok      = false;
        $message = '';

        $target = '/proc/sys/vm/drop_caches';
        if (is_writable($target)) {
            @exec('sync 2>&1');
            $bytes = @file_put_contents($target, "3\n");
            if ($bytes !== false) {
                $ok = true;
                $method = 'drop_caches';
            } else {
                $message = 'Failed to write to ' . $target;
            }
        } else {
            $cmd = 'sync && (echo 3 > /proc/sys/vm/drop_caches) 2>&1';
            @exec($cmd, $out, $rc);
            if ($rc === 0) {
                $ok = true;
                $method = 'shell';
            } else {
                $message = trim(implode("\n", (array) $out)) ?: 'drop_caches is not writable (permission denied)';
            }
        }

        $after = $this->memory()['MemAvailable'] ?? 0;
        $freed = max(0, $after - $before);

        return [
            'ok'        => $ok,
            'method'    => $method,
            'before_mb' => $before,
            'after_mb'  => $after,
            'freed_mb'  => $freed,
            'message'   => $ok ? "Freed ~{$freed} MB (via {$method})" : $message,
        ];
    }
}
