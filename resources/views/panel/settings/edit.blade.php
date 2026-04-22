@extends('panel.layouts.app')
@section('title', 'Edit Server')
@section('breadcrumb', 'Settings / '.$server->name)

@section('content')
@include('panel._flash')

<form method="POST" action="{{ route('panel.settings.update', $server) }}">
    @csrf @method('PUT')

    <div class="pw-card mb-4">
        <div class="pw-card-title">General</div>
        <div class="pw-card-body grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="pw-label">Name</label>
                <input name="name" value="{{ old('name', $server->name) }}" class="pw-input" required>
            </div>
            <div>
                <label class="pw-label">Version</label>
                <select name="version" class="pw-select">
                    @foreach (config('pw.supported_versions', ['1.7.2','1.7.4','1.7.6','1.8.0']) as $v)
                        <option value="{{ $v }}" @selected($server->version === $v)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <label class="flex items-center gap-2 text-sm md:col-span-2">
                <input type="checkbox" name="is_active" value="1" @checked($server->is_active)>
                Active
            </label>
        </div>
    </div>

    <div class="pw-card mb-4">
        <div class="pw-card-title">Daemon Socket</div>
        <div class="pw-card-body grid grid-cols-1 md:grid-cols-3 gap-3">
            <div><label class="pw-label">Host</label><input name="socket_host" value="{{ old('socket_host', $server->socket_host) }}" class="pw-input" required></div>
            <div><label class="pw-label">Port</label><input name="socket_port" type="number" value="{{ old('socket_port', $server->socket_port) }}" class="pw-input" required></div>
            <div><label class="pw-label">Auth Type</label><input name="auth_type" value="{{ old('auth_type', $server->auth_type) }}" class="pw-input" required></div>
            <div class="md:col-span-3"><label class="pw-label">Server Key</label><input name="server_key" value="{{ old('server_key', $server->server_key) }}" class="pw-input font-mono text-xs" required></div>
        </div>
    </div>

    <div class="pw-card mb-4">
        <div class="pw-card-title">Game Ports</div>
        <div class="pw-card-body grid grid-cols-2 md:grid-cols-4 gap-3">
            <div><label class="pw-label">DB Port</label><input name="db_port" type="number" value="{{ old('db_port', $server->db_port) }}" class="pw-input"></div>
            <div><label class="pw-label">Gdeliveryd</label><input name="gdeliveryd_port" type="number" value="{{ old('gdeliveryd_port', $server->gdeliveryd_port) }}" class="pw-input"></div>
            <div><label class="pw-label">Gprovider</label><input name="gprovider_port" type="number" value="{{ old('gprovider_port', $server->gprovider_port) }}" class="pw-input"></div>
            <div><label class="pw-label">Glinkd</label><input name="link_port" type="number" value="{{ old('link_port', $server->link_port) }}" class="pw-input"></div>
        </div>
    </div>

    <div class="pw-card mb-4">
        <div class="pw-card-title">Paths & Hash</div>
        <div class="pw-card-body grid grid-cols-1 md:grid-cols-2 gap-3">
            <div><label class="pw-label">Server Path</label><input name="server_path" value="{{ old('server_path', $server->server_path) }}" class="pw-input"></div>
            <div><label class="pw-label">Logs Path</label><input name="logs_path" value="{{ old('logs_path', $server->logs_path) }}" class="pw-input"></div>
            <div><label class="pw-label">Chat File</label><input name="chat_file" value="{{ old('chat_file', $server->chat_file) }}" class="pw-input"></div>
            <div>
                <label class="pw-label">Password Hash Mode</label>
                <select name="password_hash" class="pw-select">
                    @foreach (array_keys(config('pw.password_hashes', ['base64'=>'','md5'=>'','0x.md5'=>'','plain'=>''])) as $m)
                        <option value="{{ $m }}" @selected($server->password_hash === $m)>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="pw-card mb-4">
        <div class="pw-card-title">Game Database</div>
        <div class="pw-card-body grid grid-cols-1 md:grid-cols-3 gap-3">
            <div><label class="pw-label">Host</label><input name="db_host" value="{{ old('db_host', $server->db_host) }}" class="pw-input"></div>
            <div><label class="pw-label">Port</label><input name="db_port_mysql" type="number" value="{{ old('db_port_mysql', $server->db_port_mysql) }}" class="pw-input"></div>
            <div><label class="pw-label">Database</label><input name="db_name" value="{{ old('db_name', $server->db_name) }}" class="pw-input"></div>
            <div><label class="pw-label">Username</label><input name="db_user" value="{{ old('db_user', $server->db_user) }}" class="pw-input"></div>
            <div class="md:col-span-2"><label class="pw-label">Password</label><input name="db_password" type="password" placeholder="(kosongkan jika tidak diganti)" class="pw-input"></div>
        </div>
    </div>

    <div class="flex gap-2">
        <button class="pw-btn pw-btn-primary">Save Changes</button>
        <a href="{{ route('panel.settings.index') }}" class="pw-btn">Cancel</a>
    </div>
</form>
@endsection
