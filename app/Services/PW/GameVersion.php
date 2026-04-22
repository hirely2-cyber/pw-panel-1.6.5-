<?php

namespace App\Services\PW;

/**
 * Deteksi versi PW server dari gamesys.conf di glinkd (dan service lain).
 *
 * Format file:
 *   [gamesys]
 *   version=10702
 *   ...
 *
 * Kode versi 5 digit: major*10000 + minor*100 + patch.
 *   10702 → 1.7.2
 *   10706 → 1.7.6
 */
final class GameVersion
{
    /**
     * Parse integer version code into dotted form.
     * 10702 → "1.7.2"
     */
    public static function format(int $code): string
    {
        $major = intdiv($code, 10000);
        $minor = intdiv($code % 10000, 100);
        $patch = $code % 100;
        return "{$major}.{$minor}.{$patch}";
    }

    /**
     * Read version code dari gamesys.conf.
     * Mencoba multiple path service (glinkd, gdeliveryd, gamedbd).
     * Returns 0 jika tidak bisa dibaca.
     */
    public static function detectCode(?string $serverPath = null): int
    {
        $base = rtrim($serverPath ?? (string) config('pw.bootstrap.server_path', '/home'), '/');
        $candidates = [
            "{$base}/glinkd/gamesys.conf",
            "{$base}/gdeliveryd/gamesys.conf",
            "{$base}/gamedbd/gamesys.conf",
            "{$base}/gacd/gamesys.conf",
        ];

        foreach ($candidates as $path) {
            if (! is_readable($path)) continue;
            $contents = @file_get_contents($path, false, null, 0, 4096);
            if (! is_string($contents) || $contents === '') continue;
            if (preg_match('/^\s*version\s*=\s*(\d+)/mi', $contents, $m)) {
                return (int) $m[1];
            }
        }
        return 0;
    }

    /**
     * Detect dotted version ("1.7.2") or return fallback.
     */
    public static function detect(?string $serverPath = null, string $fallback = '1.7.6'): string
    {
        $code = self::detectCode($serverPath);
        return $code > 0 ? self::format($code) : $fallback;
    }
}
