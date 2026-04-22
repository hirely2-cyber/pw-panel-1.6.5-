@extends('panel.layouts.app')
@section('title', 'Audit Log')
@section('breadcrumb', 'System Settings / Audit Log')

@section('content')
<div class="pw-card">
    <div class="pw-card-title">Audit Log</div>
    <div class="pw-card-body !p-0">
        <div class="overflow-x-auto">
            <table class="pw-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>GM</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>IP</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($logs as $l)
                    <tr>
                        <td class="text-xs text-[var(--color-text-muted)] whitespace-nowrap">{{ $l->created_at }}</td>
                        <td class="font-medium">{{ $l->user->username ?? '—' }}</td>
                        <td><code>{{ $l->action }}</code></td>
                        <td class="text-xs text-[var(--color-text-muted)]">{{ $l->target_type }} {{ $l->target_id }}</td>
                        <td class="font-mono text-xs">{{ $l->ip }}</td>
                        <td>
                            @if ($l->success)<span class="pw-badge pw-badge-ok">OK</span>
                            @else<span class="pw-badge pw-badge-off">FAIL</span>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-8 text-[var(--color-text-muted)]">Belum ada log.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="pw-card-body">{{ $logs->links() }}</div>
</div>
@endsection
