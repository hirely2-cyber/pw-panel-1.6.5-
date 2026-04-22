@extends('panel.layouts.app')
@section('title', 'Settings')
@section('breadcrumb', 'Server Control / Server Settings')

@section('content')
@include('panel._flash')

<div class="pw-card">
    <div class="pw-card-title">Game Server Profiles</div>
    <div class="pw-card-body">
        <div class="text-xs text-[var(--color-text-muted)] mb-3">
            Default aktif: <strong class="text-[var(--color-accent)]">{{ $current->name }}</strong>
        </div>

        <div class="space-y-2">
            @foreach ($servers as $s)
                <div class="flex flex-wrap items-center justify-between gap-3 p-3 border border-[var(--color-border)] rounded {{ $s->is_default ? '!border-[var(--color-accent)] bg-[var(--color-accent-soft)]' : '' }}">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold">{{ $s->name }}</span>
                            @if ($s->is_default)<span class="pw-badge pw-badge-warn">Default</span>@endif
                            @if (! $s->is_active)<span class="pw-badge pw-badge-off">Inactive</span>@endif
                        </div>
                        <div class="text-xs text-[var(--color-text-muted)] mt-1">
                            v{{ $s->version }} · {{ $s->socket_host }}:{{ $s->socket_port }} · DB {{ $s->db_name }}@{{ $s->db_host }}
                        </div>
                    </div>
                    <div class="flex gap-2">
                        @if (! $s->is_default)
                            <form method="POST" action="{{ route('panel.settings.default', $s) }}">
                                @csrf
                                <button class="pw-btn pw-btn-sm">Set Default</button>
                            </form>
                        @endif
                        <a href="{{ route('panel.settings.edit', $s) }}" class="pw-btn pw-btn-sm pw-btn-primary">Edit</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
