@extends('panel.layouts.app')
@section('title', 'Chat Log')
@section('breadcrumb', 'Messaging / Chat Log')

@section('content')
<div class="pw-card">
    <div class="pw-card-title">Recent Chat Messages</div>
    <div class="pw-card-body !p-0">
        <div class="overflow-x-auto">
            <table class="pw-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Channel</th>
                        <th>From</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($logs as $l)
                    <tr>
                        <td class="font-mono text-xs whitespace-nowrap">{{ $l->time ?? '' }}</td>
                        <td><span class="pw-badge pw-badge-info">{{ $l->channel ?? '—' }}</span></td>
                        <td class="font-medium">{{ $l->srcroleName ?? $l->srcrole ?? '—' }}</td>
                        <td>{{ $l->msg ?? $l->message ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-8 text-[var(--color-text-muted)]">
                        Tabel <code>log_chat</code> tidak ditemukan atau kosong.
                    </td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
