<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PW\BackupManager;
use App\Services\PW\ProcessManager;
use App\Services\PW\Server;
use App\Services\PW\SystemInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request, ProcessManager $pm, SystemInfo $sys, BackupManager $backups)
    {
        $server = Server::current();

        // Server status via link port
        $isOnline = $pm->isOnline();

        // Memory (kalau daemon jalan)
        $mem = $pm->memory();
        $memTotal = $mem['MemTotal'] ?? 0;
        $memFree  = $mem['MemFree']  ?? 0;
        $memUsed  = max(0, $memTotal - $memFree);
        $swapTotal = $mem['SwapTotal'] ?? 0;
        $swapFree  = $mem['SwapFree']  ?? 0;
        $swapUsed  = max(0, $swapTotal - $swapFree);

        // Count account dari DB game (pw172.users)
        $accountCount = 0;
        try {
            $accountCount = DB::connection('pw_game')->table('users')->count();
        } catch (\Throwable $e) {}

        // Count character (pw172.roles)
        $charCount = 0;
        try {
            $charCount = DB::connection('pw_game')->table('roles')->count();
        } catch (\Throwable $e) {}

        return view('panel.dashboard', [
            'server'       => $server,
            'isOnline'     => $isOnline,
            'memTotal'     => $memTotal,
            'memUsed'      => $memUsed,
            'memFree'      => $memFree,
            'memPct'       => $memTotal > 0 ? round($memUsed / $memTotal * 100) : 0,
            'swapTotal'    => $swapTotal,
            'swapUsed'     => $swapUsed,
            'swapFree'     => $swapFree,
            'swapPct'      => $swapTotal > 0 ? round($swapUsed / $swapTotal * 100) : 0,
            'accountCount' => $accountCount,
            'charCount'    => $charCount,
            'sysInfo'      => $sys->all(),
            'backupList'   => $backups->list(),
            'backupDir'    => rtrim((string) config('pw.backup_dir'), '/'),
        ]);
    }

    public function status(Request $request, ProcessManager $pm)
    {
        return response()->json([
            'online'    => $pm->isOnline(),
            'memory'    => $pm->memory(),
            'services'  => $pm->status(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
