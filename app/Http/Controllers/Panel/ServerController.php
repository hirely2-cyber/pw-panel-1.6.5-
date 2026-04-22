<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Panel\AuditLog;
use App\Services\PW\BackupManager;
use App\Services\PW\ChatBroadcaster;
use App\Services\PW\MapCatalog;
use App\Services\PW\ProcessManager;
use App\Services\PW\Server;
use App\Services\PW\Socket;
use App\Services\PW\SystemInfo;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServerController extends Controller
{
    public function index(ProcessManager $pm, MapCatalog $maps, SystemInfo $sys, BackupManager $backups)
    {
        $this->authorize_('server.view');

        $server    = Server::current();
        $status    = $pm->status();
        $stopOrder = config('pw.stop_order', []);

        $allMaps     = $maps->all($server);
        $runningMaps = $maps->runningMaps($status['gs']['process'] ?? []);

        // Split: online (enriched w/ cpu/mem) vs available (not running yet)
        $onlineMaps = [];
        $availableMaps = [];
        foreach ($allMaps as $m) {
            if (isset($runningMaps[$m['id']])) {
                $onlineMaps[] = $m + [
                    'cpu' => $runningMaps[$m['id']]['cpu'],
                    'mem' => $runningMaps[$m['id']]['mem'],
                    'pid' => $runningMaps[$m['id']]['pid'],
                ];
            } else {
                $availableMaps[] = $m;
            }
        }

        // Also surface running maps that are not declared in gs.conf (edge case)
        $declaredIds = array_column($allMaps, 'id');
        foreach ($runningMaps as $id => $info) {
            if (! in_array($id, $declaredIds, true)) {
                $onlineMaps[] = [
                    'id'       => $id,
                    'name'     => config("pw_locations.$id", "[{$id}] Unknown"),
                    'category' => 'other',
                    'cpu'      => $info['cpu'],
                    'mem'      => $info['mem'],
                    'pid'      => $info['pid'],
                ];
            }
        }

        $gsConfPath = rtrim((string) $server->server_path, '/').'/gamed/gs.conf';

        // --- System memory (RAM/Swap) via daemon or local fallback ---
        $mem = $pm->memory();
        $memTotal  = (int) ($mem['MemTotal']  ?? 0);
        $memFree   = (int) ($mem['MemFree']   ?? 0);
        $memAvail  = (int) ($mem['MemAvailable'] ?? $memFree);
        $buffers   = (int) ($mem['Buffers']   ?? 0);
        $cached    = (int) ($mem['Cached']    ?? 0);
        $buffCache = $buffers + $cached;
        // Apps = yang benar-benar dipakai app (non-reclaimable).
        // MemAvailable sudah termasuk reclaimable cache, jadi formulanya cukup:
        //   apps = total - available
        $apps      = max(0, $memTotal - $memAvail);
        $swapTotal = (int) ($mem['SwapTotal'] ?? 0);
        $swapFree  = (int) ($mem['SwapFree']  ?? 0);
        $swapUsed  = max(0, $swapTotal - $swapFree);

        $memory = [
            'total'      => $memTotal,
            'apps'       => $apps,
            'buff_cache' => $buffCache,
            'available'  => $memAvail,
            'apps_pct'       => $memTotal ? round($apps / $memTotal * 100)      : 0,
            'buff_cache_pct' => $memTotal ? round($buffCache / $memTotal * 100) : 0,
            'swap_total' => $swapTotal,
            'swap_used'  => $swapUsed,
            'swap_pct'   => $swapTotal ? round($swapUsed / $swapTotal * 100) : 0,
        ];

        return view('panel.server.index', compact(
            'server', 'status', 'stopOrder',
            'allMaps', 'runningMaps', 'onlineMaps', 'availableMaps',
            'gsConfPath', 'memory'
        ) + [
            'sysInfo'     => $sys->all(),
            'backupList'  => $backups->list(),
            'backupDir'   => rtrim((string) config('pw.backup_dir'), '/'),
        ]);
    }

    public function downloadBackup(string $name, BackupManager $backups)
    {
        $this->authorize_('server.control');

        $file = $backups->find($name);
        abort_unless($file, 404, 'Backup not found');

        AuditLog::create([
            'gm_user_id' => auth('panel')->id(),
            'action'     => 'server.backup.download',
            'target_id'  => $name,
            'ip'         => request()->ip(),
            'user_agent' => (string) request()->userAgent(),
            'payload'    => ['name' => $name, 'size' => $file['size']],
        ]);

        return response()->download($file['path'], $file['name']);
    }

    public function deleteBackup(string $name, BackupManager $backups)
    {
        $this->authorize_('server.control');

        $file = $backups->find($name);
        abort_unless($file, 404, 'Backup not found');

        @unlink($file['path']);

        AuditLog::create([
            'gm_user_id' => auth('panel')->id(),
            'action'     => 'server.backup.delete',
            'target_id'  => $name,
            'ip'         => request()->ip(),
            'user_agent' => (string) request()->userAgent(),
            'payload'    => ['name' => $name],
        ]);

        return back()->with('ok', "Backup deleted: {$name}.");
    }

    public function control(Request $request, ProcessManager $pm, BackupManager $backups)
    {
        $this->authorize_('server.control');

        $action = $request->input('action');
        $svc    = $request->input('service', 'all');
        $mapIds = (array) $request->input('maps', []);
        $target = $mapIds ? ('maps:'.implode(',', $mapIds)) : $svc;

        $log = AuditLog::create([
            'gm_user_id' => auth('panel')->id(),
            'action'     => "server.{$action}",
            'target_id'  => $target,
            'ip'         => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'payload'    => compact('action', 'svc', 'mapIds'),
        ]);

        if ($action === 'start') {
            @set_time_limit(120);

            // Map-specific start (service=gs + maps[])
            if ($svc === 'gs' && ! empty($mapIds)) {
                $delay = (int) $request->input('delay', 0);
                $res = $pm->startMaps($mapIds, $delay);
                $n = count($res['started']);
                if ($res['ok']) {
                    return back()->with('ok', "Started {$n} map(s) (log #{$log->id}).");
                }
                $list = implode(', ', $res['failed']);
                return back()->with('error', "Some maps failed: {$list} (log #{$log->id}).");
            }

            $res = $pm->startAll();
            if ($res['ok']) {
                return back()->with('ok', "Server starting… all services sent (log #{$log->id}).");
            }
            $list = implode(', ', $res['failed']);
            return back()->with('error', "Start failed for: {$list} (log #{$log->id}).");
        }

        if ($action === 'stop') {
            // Map-specific stop (service=gs + maps[])
            if ($svc === 'gs' && ! empty($mapIds)) {
                $delay = (int) $request->input('delay', 0);
                $res = $pm->stopMaps($mapIds, $delay);
                $n = count($res['killed']);
                if ($res['ok']) {
                    return back()->with('ok', "Stopped {$n} map(s) (log #{$log->id}).");
                }
                $list = implode(', ', $res['failed']);
                return back()->with('error', "Some maps failed to stop: {$list} (log #{$log->id}).");
            }

            // Stop ONLY gs (all maps + gs daemon) — dipakai Delay Stop
            if ($svc === 'gs') {
                $res = $pm->stopGs();
                if ($res['ok']) {
                    return back()->with('ok', "All maps stopped (gs shut down, log #{$log->id}).");
                }
                return back()->with('error', "Stop gs failed (log #{$log->id}).");
            }

            $res = $pm->stopAll();
            if ($res['ok']) {
                return back()->with('ok', "Server stopping… all services stopped (log #{$log->id}).");
            }
            $list = implode(', ', $res['failed']);
            return back()->with('error', "Stop failed for: {$list} (log #{$log->id}).");
        }

        if ($action === 'restart') {
            $pm->stopAll();
            sleep(2);
            $res = $pm->startAll();
            if ($res['ok']) {
                return back()->with('ok', "Server restarting… (log #{$log->id}).");
            }
            $list = implode(', ', $res['failed']);
            return back()->with('error', "Restart failed for: {$list} (log #{$log->id}).");
        }

        if ($action === 'clear_cache') {
            $res = $pm->clearCache();
            if ($res['ok']) {
                return back()->with('ok', "RAM cache cleared · freed {$res['freed_mb']} MB (before {$res['before_mb']} MB → after {$res['after_mb']} MB available, via {$res['method']}, log #{$log->id}).");
            }
            return back()->with('error', "Clear RAM failed: {$res['message']} (log #{$log->id}).");
        }

        if ($action === 'backup') {
            @set_time_limit(1800);
            @ini_set('memory_limit', '512M');
            $sql    = $backups->backup();
            $server = $backups->backupServer();

            $ok = $sql['ok'] && $server['ok'];
            $parts = [];
            if ($sql['ok']) {
                $mb = number_format($sql['size'] / 1024 / 1024, 2);
                $parts[] = "SQL: {$sql['name']} ({$mb} MB)";
            } else {
                $parts[] = "SQL failed: {$sql['message']}";
            }
            if ($server['ok']) {
                $mb = number_format($server['size'] / 1024 / 1024, 2);
                $parts[] = "Server files: {$server['name']} ({$mb} MB)";
            } else {
                $parts[] = "Server files failed: {$server['message']}";
            }
            $msg = implode(' · ', $parts) . " (log #{$log->id}).";
            return back()->with($ok ? 'ok' : 'error', $msg);
        }

        return back()->with('ok', "Action '{$action}' logged (log #{$log->id}).");
    }

    /**
     * Send a single in-game chat broadcast. Used by Delay Stop countdown
     * to warn players (e.g. "Server shutdown in 5 minutes").
     */
    public function broadcast(Request $request, ChatBroadcaster $chat)
    {
        $this->authorize_('server.control');

        $data = $request->validate([
            'message' => ['required', 'string', 'max:200'],
            'channel' => ['nullable', 'integer', 'between:0,15'],
        ]);
        $channel = (int) ($data['channel'] ?? ChatBroadcaster::CH_SYSTEM);
        $ok = $chat->send($data['message'], $channel);

        return response()->json([
            'ok'      => $ok,
            'channel' => $channel,
            'message' => $data['message'],
        ]);
    }

    protected function authorize_(string $perm): void
    {
        if (! auth('panel')->user()->can($perm)) {
            abort(403, "Missing permission: {$perm}");
        }
    }

    public function controlStream(Request $request, ProcessManager $pm)
    {
        $this->authorize_('server.control');

        $action = $request->input('action');
        if (! in_array($action, ['start', 'stop', 'restart'], true)) {
            abort(400, 'Invalid action');
        }

        AuditLog::create([
            'gm_user_id' => auth('panel')->id(),
            'action'     => "server.{$action}.stream",
            'target_id'  => 'all',
            'ip'         => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'payload'    => ['action' => $action],
        ]);

        return new StreamedResponse(function () use ($action, $pm) {
            @set_time_limit(180);
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', 'off');
            while (ob_get_level() > 0) { ob_end_flush(); }

            $send = function (string $event, array $data) {
                echo "event: {$event}\n";
                echo 'data: ' . json_encode($data) . "\n\n";
                @ob_flush(); @flush();
            };

            $onProgress = function (string $key, string $phase, array $extra) use ($send) {
                $send('progress', ['key' => $key, 'phase' => $phase] + $extra);
            };

            $send('hello', ['action' => $action]);

            try {
                if ($action === 'start') {
                    $pm->startAll($onProgress);
                } elseif ($action === 'stop') {
                    $pm->stopAll($onProgress);
                } else { // restart
                    $pm->stopAll($onProgress);
                    sleep(2);
                    $pm->startAll($onProgress);
                }
            } catch (\Throwable $e) {
                $send('error', ['message' => $e->getMessage()]);
            }

            $send('end', ['ok' => true]);
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }
}
