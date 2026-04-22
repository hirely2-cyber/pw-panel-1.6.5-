@extends('panel.layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
@include('panel._server-info')

<div class="space-y-5">

    {{-- Server header --}}
    <div class="pw-card">
        <div class="pw-card-title">Current Server</div>
        <div class="pw-card-body flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-lg font-semibold text-[var(--color-text)]">{{ $server->name }}</div>
                <div class="text-xs text-[var(--color-text-muted)] mt-0.5">
                    v{{ $server->version }} &nbsp;·&nbsp; {{ $server->socket_host }}:{{ $server->socket_port }}
                </div>
            </div>
            @if ($isOnline)
                <span class="pw-badge pw-badge-ok">● Online</span>
            @else
                <span class="pw-badge pw-badge-off">● Offline</span>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="pw-card p-4">
            <div class="text-xs text-[var(--color-text-muted)] mb-1 flex items-center justify-between">
                <span>Accounts</span>
                <span class="pw-badge pw-badge-info">Real-time</span>
            </div>
            <div class="text-2xl font-semibold text-[var(--color-accent)]">{{ number_format($accountCount) }}</div>
            <div class="text-[11px] text-[var(--color-text-muted)] mt-0.5">Total accounts in DB</div>
        </div>
        <div class="pw-card p-4">
            <div class="text-xs text-[var(--color-text-muted)] mb-1 flex items-center justify-between">
                <span>Characters</span>
                <span class="pw-badge pw-badge-info">Real-time</span>
            </div>
            <div class="text-2xl font-semibold text-[var(--color-accent)]">{{ number_format($charCount) }}</div>
            <div class="text-[11px] text-[var(--color-text-muted)] mt-0.5">Total characters</div>
        </div>
        <div class="pw-card p-4">
            <div class="text-xs text-[var(--color-text-muted)] mb-1 flex items-center justify-between">
                <span>Memory</span>
                <span class="pw-badge pw-badge-muted">{{ $memPct }}%</span>
            </div>
            <div class="text-2xl font-semibold text-[var(--color-text)]">{{ $memUsed }}<span class="text-sm text-[var(--color-text-muted)]"> / {{ $memTotal }} MB</span></div>
            <div class="mt-2 h-1.5 bg-[var(--color-surface-2)] rounded-full overflow-hidden">
                <div class="h-full" style="width:{{ $memPct }}%; background: var(--color-primary)"></div>
            </div>
        </div>
        <div class="pw-card p-4">
            <div class="text-xs text-[var(--color-text-muted)] mb-1 flex items-center justify-between">
                <span>Swap</span>
                <span class="pw-badge pw-badge-muted">{{ $swapPct }}%</span>
            </div>
            <div class="text-2xl font-semibold text-[var(--color-text)]">{{ $swapUsed }}<span class="text-sm text-[var(--color-text-muted)]"> / {{ $swapTotal }} MB</span></div>
            <div class="mt-2 h-1.5 bg-[var(--color-surface-2)] rounded-full overflow-hidden">
                <div class="h-full" style="width:{{ $swapPct }}%; background: var(--color-danger)"></div>
            </div>
        </div>
    </div>

    {{-- Quick access --}}
    <div class="pw-card">
        <div class="pw-card-title">Quick Access</div>
        <div class="pw-card-body grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @php
                $quick = [
                    ['panel.accounts.index',   'Accounts',     'accounts',   'account.view'],
                    ['panel.characters.index', 'Characters',   'characters', 'character.view'],
                    ['panel.server.index',     'Server',       'server',     'server.view'],
                    ['panel.settings.index',   'Settings',     'settings',   'panel.settings'],
                    ['panel.mail.index',       'Mail',         'mail',       'mail.send'],
                    ['panel.chat.index',       'Chat Log',     'chat',       'chat.read'],
                ];
                $u = auth('panel')->user();
            @endphp
            @foreach ($quick as [$r, $l, $i, $p])
                @if (! $p || $u->can($p))
                    <a href="{{ route($r) }}"
                       class="flex flex-col items-center justify-center gap-2 p-4 rounded border border-[var(--color-border)] hover:border-[var(--color-primary)] hover:text-[var(--color-primary)] transition">
                        @include('panel._icon', ['name' => $i, 'size' => 26])
                        <span class="text-xs font-medium">{{ $l }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Server config --}}
        <div class="pw-card">
            <div class="pw-card-title">Server Configuration</div>
            <div class="pw-card-body">
                <table class="pw-table">
                    <tbody>
                        <tr><td class="!w-40 text-[var(--color-text-muted)]">Name</td><td>{{ $server->name }}</td></tr>
                        <tr><td class="text-[var(--color-text-muted)]">Version</td><td>{{ $server->version }}</td></tr>
                        <tr><td class="text-[var(--color-text-muted)]">Auth Service</td><td>{{ $server->auth_type }}</td></tr>
                        <tr><td class="text-[var(--color-text-muted)]">Link Port</td><td>{{ $server->link_port }}</td></tr>
                        <tr><td class="text-[var(--color-text-muted)]">Password Hash</td><td>{{ $server->password_hash }}</td></tr>
                        <tr><td class="text-[var(--color-text-muted)]">Server Path</td><td><code>{{ $server->server_path }}</code></td></tr>
                        <tr><td class="text-[var(--color-text-muted)]">Logs Path</td><td><code>{{ $server->logs_path }}</code></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Todo / Notes --}}
        <div class="pw-card">
            <div class="pw-card-title">Roadmap</div>
            <div class="pw-card-body text-sm text-[var(--color-text-soft)] space-y-1.5">
                <div>• Port <code>Stream.php</code> + <code>GRole.php</code> untuk character editor binary</div>
                <div>• Implementasi real socket dispatch pada Server Control</div>
                <div>• 2FA untuk akun GM</li>
                <div>• Integrasi ECharts untuk grafik distribusi kelas</div>
            </div>
        </div>
    </div>
</div>

<div class="mt-5">
    @include('panel._backup-list')
</div>
@endsection
