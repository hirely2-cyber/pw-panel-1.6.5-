<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Panel\GameServer;
use App\Services\PW\Server as PwServer;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $this->authorize_('panel.settings');

        $servers = GameServer::orderBy('is_default', 'desc')->orderBy('name')->get();
        $current = PwServer::current();

        return view('panel.settings.index', compact('servers', 'current'));
    }

    public function edit(GameServer $server)
    {
        $this->authorize_('panel.servers');
        return view('panel.settings.edit', compact('server'));
    }

    public function update(Request $request, GameServer $server)
    {
        $this->authorize_('panel.servers');

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:80'],
            'version'         => ['required', 'string', 'in:1.7.2,1.7.4,1.7.6,1.8.0'],
            'socket_host'     => ['required', 'string', 'max:80'],
            'socket_port'     => ['required', 'integer', 'between:1,65535'],
            'server_key'      => ['required', 'string', 'max:128'],
            'db_port'         => ['required', 'integer', 'between:1,65535'],
            'gdeliveryd_port' => ['required', 'integer', 'between:1,65535'],
            'gprovider_port'  => ['required', 'integer', 'between:1,65535'],
            'link_port'       => ['required', 'integer', 'between:1,65535'],
            'server_path'     => ['required', 'string', 'max:255'],
            'logs_path'       => ['required', 'string', 'max:255'],
            'auth_type'       => ['required', 'string', 'max:32'],
            'chat_file'       => ['required', 'string', 'max:64'],
            'password_hash'   => ['required', 'in:base64,md5,0x.md5,plain'],
            'db_host'         => ['required', 'string', 'max:80'],
            'db_port_mysql'   => ['required', 'integer', 'between:1,65535'],
            'db_name'         => ['required', 'string', 'max:64'],
            'db_user'         => ['required', 'string', 'max:64'],
            'db_password'     => ['nullable', 'string', 'max:255'],
            'is_active'       => ['sometimes', 'boolean'],
        ]);

        $server->update($data);
        PwServer::flush();

        return redirect()->route('panel.settings.index')->with('ok', 'Konfigurasi server diperbarui.');
    }

    public function setDefault(GameServer $server)
    {
        $this->authorize_('panel.servers');
        GameServer::query()->update(['is_default' => false]);
        $server->update(['is_default' => true]);
        PwServer::flush();
        return back()->with('ok', "Server '{$server->name}' di-set sebagai default.");
    }

    protected function authorize_(string $perm): void
    {
        if (! auth('panel')->user()->can($perm)) {
            abort(403, "Missing permission: {$perm}");
        }
    }
}
