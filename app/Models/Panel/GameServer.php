<?php

namespace App\Models\Panel;

use Illuminate\Database\Eloquent\Model;

class GameServer extends Model
{
    protected $table = 'game_servers';

    protected $fillable = [
        'name', 'slug', 'version', 'is_default', 'is_active',
        'socket_host', 'socket_port', 'server_key',
        'db_port', 'gdeliveryd_port', 'gprovider_port', 'link_port',
        'server_path', 'logs_path', 'auth_type', 'chat_file', 'password_hash',
        'db_host', 'db_port_mysql', 'db_name', 'db_user', 'db_password',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    protected $hidden = ['server_key', 'db_password'];

    public static function default(): ?self
    {
        return static::where('is_default', true)->where('is_active', true)->first()
            ?? static::where('is_active', true)->orderBy('id')->first();
    }
}
