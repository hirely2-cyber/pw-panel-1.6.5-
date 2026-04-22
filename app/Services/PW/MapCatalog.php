<?php

namespace App\Services\PW;

use Illuminate\Support\Facades\Log;

/**
 * Perfect World map (location) catalog.
 *
 * Source of truth (identical to iweb/system/libs/func.php::listLocations):
 *   1. Read gs.conf from game server daemon via Socket opcode 57
 *   2. Parse [General] world_servers + instance_servers (semicolon-separated)
 *   3. Resolve display name via config('pw_locations') (= iweb lang::$locations)
 *
 * On socket failure, fallback to local filesystem read of gs.conf if reachable.
 * If both unavailable, returns empty list — NOT a hardcoded map list.
 */
class MapCatalog
{
    public function __construct(private readonly ?Socket $socket = null) {}

    public static function forCurrent(): self
    {
        return new self(Socket::fromCurrent());
    }

    /**
     * @return list<array{id:string, name:string, category:string}>
     */
    public function all(?object $server = null): array
    {
        $server ??= Server::current();
        $conf   = $this->fetchGsConf($server);

        if (! $conf) {
            return [];
        }

        return $this->decorate($this->extractLocationIds($conf));
    }

    /**
     * Running map instances extracted from `ps`-style rows returned by
     * ProcessManager::status()['gs']['process']. Column 11 = first argv after
     * gs program name = map id (e.g. "gs01", "is23").
     *
     * @return array<string, array{pid:int, cpu:string, mem:string}>
     */
    public function runningMaps(array $gsProcesses): array
    {
        $out = [];
        foreach ($gsProcesses as $row) {
            if (! is_array($row) || count($row) < 12) {
                continue;
            }
            $mapId = trim((string) $row[11]);
            if ($mapId === '') {
                continue;
            }
            $out[$mapId] = [
                'pid' => (int) ($row[1] ?? 0),
                'cpu' => (string) ($row[2] ?? '0'),
                'mem' => (string) ($row[3] ?? '0'),
            ];
        }
        return $out;
    }

    // ----------------------------------------------------------------- helpers

    protected function fetchGsConf(object $server): ?string
    {
        $path = $this->gsConfPath($server);

        // 1) via daemon (same as iweb: socket::sendPacket(57, packString(path)))
        if ($this->socket) {
            try {
                $raw = $this->socket->sendPacket(57, Socket::packString($path), 2048 * 1000);
                if ($raw !== '' && $raw !== 'File not found' && ! str_starts_with($raw, 'key:0')) {
                    return $raw;
                }
            } catch (\Throwable $e) {
                Log::info("MapCatalog: socket read gs.conf failed — {$e->getMessage()}");
            }
        }

        // 2) fallback to local filesystem (when panel runs on same host as PW)
        if (is_readable($path)) {
            $raw = @file_get_contents($path);
            if ($raw !== false && $raw !== '') {
                return $raw;
            }
        }

        return null;
    }

    protected function gsConfPath(object $server): string
    {
        $explicit = trim((string) ($server->gs_conf_path ?? ''));
        if ($explicit !== '') {
            return $explicit;
        }
        $root = rtrim((string) ($server->server_path ?? ''), '/');
        if ($root === '') {
            return '/home/gamed/gs.conf'; // iweb default
        }
        return "{$root}/gamed/gs.conf";
    }

    /**
     * Parse gs.conf INI content and return map ids from
     * General.world_servers + General.instance_servers (iweb logic).
     *
     * @return list<string>
     */
    protected function extractLocationIds(string $raw): array
    {
        $section = null;
        $data = ['General' => []];
        foreach (preg_split("/\r?\n/", $raw) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === ';' || $line[0] === '#') {
                continue;
            }
            if (preg_match('/^\[(.+?)\]$/', $line, $m)) {
                $section = $m[1];
                $data[$section] ??= [];
                continue;
            }
            if ($section !== null && str_contains($line, '=')) {
                [$k, $v] = explode('=', $line, 2);
                $data[$section][trim($k)] = trim($v);
            }
        }

        $world    = (string) ($data['General']['world_servers']    ?? '');
        $instance = (string) ($data['General']['instance_servers'] ?? '');
        $joined   = trim($world.';'.$instance, '; ');

        $ids = array_values(array_filter(array_map('trim', explode(';', $joined))));
        return array_values(array_unique($ids));
    }

    /**
     * @param  list<string> $ids
     * @return list<array{id:string, name:string, category:string}>
     */
    protected function decorate(array $ids): array
    {
        $names = config('pw_locations', []);
        $out = [];
        foreach ($ids as $id) {
            $out[] = [
                'id'       => $id,
                'name'     => $names[$id] ?? "[{$id}] Unknown location",
                'category' => $this->categoryOf($id),
            ];
        }
        return $out;
    }

    protected function categoryOf(string $id): string
    {
        return match (true) {
            str_starts_with($id, 'gs')    => 'world',
            str_starts_with($id, 'is')    => 'instance',
            str_starts_with($id, 'arena') => 'arena',
            str_starts_with($id, 'bg')    => 'battleground',
            str_starts_with($id, 'rand')  => 'random',
            default                        => 'other',
        };
    }
}
