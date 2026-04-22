<?php

namespace App\Services\PW;

use App\Models\Panel\GameServer;
use Illuminate\Support\Facades\Cache;

/**
 * Current active game server profile.
 * Dipakai semua service PW (Socket, GRole, dll).
 */
final class Server
{
    private static ?GameServer $instance = null;

    public static function current(): GameServer
    {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = Cache::remember('pw.current_server', 60, function () {
            $server = GameServer::default();

            if (! $server) {
                // Bootstrap dari .env bila belum ada record
                $boot = config('pw.bootstrap');

                $server = GameServer::create([
                    'name'            => $boot['name'],
                    'slug'            => 'default',
                    'version'         => $boot['version'],
                    'is_default'      => true,
                    'is_active'       => true,
                    'socket_host'     => $boot['socket_host'],
                    'socket_port'     => $boot['socket_port'],
                    'server_key'      => $boot['server_key'],
                    'db_port'         => $boot['db_port'],
                    'gdeliveryd_port' => $boot['gdeliveryd_port'],
                    'gprovider_port'  => $boot['gprovider_port'],
                    'link_port'       => $boot['link_port'],
                    'server_path'     => $boot['server_path'],
                    'logs_path'       => $boot['logs_path'],
                    'auth_type'       => $boot['auth_type'],
                    'chat_file'       => $boot['chat_file'],
                    'password_hash'   => $boot['password_hash'],
                    'db_host'         => env('PW_DB_HOST', '127.0.0.1'),
                    'db_port_mysql'   => (int) env('PW_DB_PORT', 3306),
                    'db_name'         => env('PW_DB_DATABASE', 'pw172'),
                    'db_user'         => env('PW_DB_USERNAME', 'root'),
                    'db_password'     => env('PW_DB_PASSWORD', ''),
                ]);
            }

            return $server;
        });

        return self::$instance;
    }

    public static function use(GameServer $server): void
    {
        self::$instance = $server;
        Cache::put('pw.current_server', $server, 60);
    }

    public static function flush(): void
    {
        self::$instance = null;
        Cache::forget('pw.current_server');
    }
}
