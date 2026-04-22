<?php

namespace App\Services\PW;

/**
 * System info gathered from local /proc and shell commands.
 * Used by the Server Control dashboard header.
 */
class SystemInfo
{
    /**
     * @return array{
     *     host: string,
     *     os: string,
     *     cpu_cores: int,
     *     cpu_model: string,
     *     load: array{1:float,5:float,15:float},
     *     disk_total: int,
     *     disk_used: int,
     *     disk_pct: int,
     *     uptime_seconds: int,
     *     uptime_human: string
     * }
     */
    public function all(): array
    {
        return [
            'host'           => gethostname() ?: 'unknown',
            'os'             => $this->os(),
            'cpu_cores'      => $this->cpuCores(),
            'cpu_model'      => $this->cpuModel(),
            'load'           => $this->loadAvg(),
            'disk_total'     => (int) round(@disk_total_space('/') / (1024 ** 3)),
            'disk_used'      => (int) round((@disk_total_space('/') - @disk_free_space('/')) / (1024 ** 3)),
            'disk_pct'       => $this->diskPct(),
            'uptime_seconds' => $this->uptimeSeconds(),
            'uptime_human'   => $this->uptimeHuman(),
        ];
    }

    protected function os(): string
    {
        if (is_readable('/etc/os-release')) {
            $raw = (string) @file_get_contents('/etc/os-release');
            if (preg_match('/^PRETTY_NAME="?([^"\n]+)"?/m', $raw, $m)) {
                return $m[1];
            }
        }
        return PHP_OS_FAMILY;
    }

    protected function cpuCores(): int
    {
        if (is_readable('/proc/cpuinfo')) {
            $raw = (string) @file_get_contents('/proc/cpuinfo');
            return max(1, substr_count($raw, "\nprocessor\t") + (str_starts_with($raw, 'processor') ? 1 : 0));
        }
        return 1;
    }

    protected function cpuModel(): string
    {
        if (is_readable('/proc/cpuinfo')) {
            $raw = (string) @file_get_contents('/proc/cpuinfo');
            if (preg_match('/^model name\s*:\s*(.+)$/m', $raw, $m)) {
                return trim($m[1]);
            }
        }
        return 'Unknown CPU';
    }

    /** @return array{1:float,5:float,15:float} */
    protected function loadAvg(): array
    {
        if (function_exists('sys_getloadavg')) {
            [$a, $b, $c] = sys_getloadavg() + [0, 0, 0];
            return [1 => (float) $a, 5 => (float) $b, 15 => (float) $c];
        }
        return [1 => 0.0, 5 => 0.0, 15 => 0.0];
    }

    protected function diskPct(): int
    {
        $total = @disk_total_space('/');
        $free  = @disk_free_space('/');
        if (! $total) {
            return 0;
        }
        return (int) round(($total - $free) / $total * 100);
    }

    protected function uptimeSeconds(): int
    {
        if (is_readable('/proc/uptime')) {
            $raw = (string) @file_get_contents('/proc/uptime');
            return (int) floatval(strtok($raw, ' '));
        }
        return 0;
    }

    protected function uptimeHuman(): string
    {
        $s = $this->uptimeSeconds();
        if ($s <= 0) {
            return 'unknown';
        }
        $w = intdiv($s, 604800); $s %= 604800;
        $d = intdiv($s, 86400);  $s %= 86400;
        $h = intdiv($s, 3600);   $s %= 3600;
        $m = intdiv($s, 60);

        $parts = [];
        if ($w) $parts[] = "{$w} week".($w > 1 ? 's' : '');
        if ($d) $parts[] = "{$d} day".($d > 1 ? 's' : '');
        if ($h) $parts[] = "{$h} hour".($h > 1 ? 's' : '');
        if ($m && ! $w) $parts[] = "{$m} minute".($m > 1 ? 's' : '');

        return $parts ? implode(', ', $parts) : '<1 minute';
    }
}
