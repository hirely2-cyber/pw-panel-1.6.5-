<?php

namespace App\Services\PW\Roles;

use App\Services\PW\GameVersion;
use App\Services\PW\Server;
use App\Services\PW\Socket;
use App\Services\PW\PwSocketException;

/**
 * High-level facade untuk fetch character data dari game server.
 */
class RoleService
{
    /** version code → struct class FQCN */
    private const STRUCT_MAP = [
        '1.7.2' => \App\Services\PW\Roles\Structs\Character174::class,
        '1.7.4' => \App\Services\PW\Roles\Structs\Character174::class,
        '1.7.6' => \App\Services\PW\Roles\Structs\Character174::class,
    ];

    public static function resolveStructClass(?string $version = null): string
    {
        $version ??= GameVersion::detect();
        return self::STRUCT_MAP[$version] ?? \App\Services\PW\Roles\Structs\Character174::class;
    }

    /**
     * Fetch + decode role dari gamedbd. Returns null kalau gagal/timeout.
     */
    public function fetch(int $roleId, ?string $version = null): ?array
    {
        $struct = self::resolveStructClass($version);
        GRoleReader::setStruct($struct);
        GRoleReader::$cycle = false;

        $srv  = Server::current();
        $sock = Socket::fromCurrent();

        try {
            $out = GRoleReader::readCharacter($sock, (int) $srv->db_port, $roleId);
        } catch (PwSocketException $e) {
            report($e);
            return null;
        }

        if (empty($out['role']) || empty($out['role']['base'] ?? null)) {
            return null;
        }
        return $out;
    }

    /**
     * Ringkasan untuk show page: hanya field yg dibutuhkan UI.
     */
    public function summary(array $role): array
    {
        $base   = $role['role']['base']   ?? [];
        $status = $role['role']['status'] ?? [];
        $pocket = $role['role']['pocket'] ?? [];

        $valueOf = fn($node, $def = null) => is_array($node) && array_key_exists('value', $node) ? $node['value'] : $def;

        return [
            'name'           => $valueOf($base['name'] ?? null, ''),
            'spouse'         => (int) $valueOf($base['spouse'] ?? null, 0),
            'cls'            => (int) $valueOf($base['cls'] ?? null, 0),
            'race'           => (int) $valueOf($base['race'] ?? null, 0),
            'gender'         => (int) $valueOf($base['gender'] ?? null, 0),
            'custom_stamp'   => (int) $valueOf($base['custom_stamp'] ?? null, 0),
            'level'          => (int) $valueOf($status['level'] ?? null, 0),
            'level2'         => (int) $valueOf($status['level2'] ?? null, 0),
            'exp'            => (int) $valueOf($status['exp'] ?? null, 0),
            'sp'             => (int) $valueOf($status['sp'] ?? null, 0),
            'pp'             => (int) $valueOf($status['pp'] ?? null, 0),
            'hp'             => (int) $valueOf($status['hp'] ?? null, 0),
            'mp'             => (int) $valueOf($status['mp'] ?? null, 0),
            'reputation'     => (int) $valueOf($status['reputation'] ?? null, 0),
            'worldtag'       => (int) $valueOf($status['worldtag'] ?? null, 0),
            'posx'           => (float) $valueOf($status['posx'] ?? null, 0),
            'posy'           => (float) $valueOf($status['posy'] ?? null, 0),
            'posz'           => (float) $valueOf($status['posz'] ?? null, 0),
            'invader_state'  => (int) $valueOf($status['invader_state'] ?? null, 0),
            'invader_time'   => (int) $valueOf($status['invader_time'] ?? null, 0),
            'pariah_time'    => (int) $valueOf($status['pariah_time'] ?? null, 0),
            'create_time'      => (int) $valueOf($base['create_time'] ?? null, 0),
            'lastlogin_time'  => (int) $valueOf($base['lastlogin_time'] ?? null, 0),
            'delete_time'     => (int) $valueOf($base['delete_time'] ?? null, 0),
            'char_status'     => (int) $valueOf($base['status'] ?? null, 1),
            'money'           => (int) $valueOf($pocket['money'] ?? null, 0),
            'storehouse_money'=> (int) $valueOf(($role['role']['storehouse']['money'] ?? null), 0),

            // 天脉 / Sky / Realm (expansion cultivation)
            'realm_level'    => (int) ($status['realm_data']['level']['value']    ?? 0),
            'realm_exp'      => (int) ($status['realm_data']['exp']['value']      ?? 0),

            // property block (vitality/energy/strength/agility/etc)
            'property'       => self::flattenValues($status['property'] ?? []),

            // vardata (pvp_cooldown, pk_count, etc)
            'var_data'       => self::flattenValues($status['var_data'] ?? []),

            // Inventory containers
            'pocket'     => self::extractContainer($role['role']['pocket']     ?? [], 'inv',      'icapacity'),
            'equipment'  => self::extractContainer($role['role']['equipment']  ?? [], 'eqp',      null),
            'storehouse' => self::extractContainer($role['role']['storehouse'] ?? [], 'store',    'capacity'),
            'wardrobe'   => self::extractContainer($role['role']['storehouse'] ?? [], 'dress',    'size1'),
            'material'   => self::extractContainer($role['role']['storehouse'] ?? [], 'material', 'size2'),
            'card'       => self::extractContainer($role['role']['storehouse'] ?? [], 'card',     'size3'),
        ];
    }

    /**
     * Normalize container slot list. Returns
     * ['capacity'=>int,'used'=>int,'items'=>[['pos','id','count','max_count','proctype','expire_date','guid1','guid2','mask','data']...]]
     */
    private static function extractContainer(array $node, string $listKey, ?string $capacityKey): array
    {
        $items = [];
        foreach (($node[$listKey] ?? []) as $slot) {
            if (!is_array($slot)) continue;
            $id = (int) ($slot['id']['value'] ?? 0);
            if ($id <= 0) continue;
            $rawData = $slot['data']['value'] ?? '';
            $items[] = [
                'id'          => $id,
                'pos'         => (int) ($slot['pos']['value'] ?? 0),
                'count'       => (int) ($slot['count']['value'] ?? 0),
                'max_count'   => (int) ($slot['max_count']['value'] ?? 0),
                'proctype'    => (int) ($slot['proctype']['value'] ?? 0),
                'expire_date' => (int) ($slot['expire_date']['value'] ?? 0),
                'guid1'       => (int) ($slot['guid1']['value'] ?? 0),
                'guid2'       => (int) ($slot['guid2']['value'] ?? 0),
                'mask'        => (int) ($slot['mask']['value'] ?? 0),
                'data'        => $rawData !== '' ? bin2hex($rawData) : '',
            ];
        }
        $capacity = $capacityKey ? (int) ($node[$capacityKey]['value'] ?? 0) : count($items);
        return [
            'capacity' => $capacity,
            'used'     => count($items),
            'items'    => $items,
        ];
    }

    private static function flattenValues(array $node): array
    {
        $out = [];
        foreach ($node as $k => $v) {
            if (is_array($v) && array_key_exists('value', $v)) {
                $out[$k] = $v['value'];
            } elseif (is_array($v)) {
                $out[$k] = self::flattenValues($v);
            }
        }
        return $out;
    }
}
