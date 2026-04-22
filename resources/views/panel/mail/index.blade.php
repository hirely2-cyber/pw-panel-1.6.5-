@extends('panel.layouts.app')
@section('title', 'Mail')
@section('breadcrumb', 'Messaging / Send Mail')

@section('content')
@include('panel._flash')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    <div class="pw-card">
        <div class="pw-card-title">Compose Mail</div>
        <div class="pw-card-body">
            <form method="POST" action="{{ route('panel.mail.send') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="pw-label">Recipient (character name)</label>
                    <input name="recipient_name" value="{{ old('recipient_name') }}" class="pw-input" required>
                </div>
                <div>
                    <label class="pw-label">Subject</label>
                    <input name="title" value="{{ old('title') }}" class="pw-input" required>
                </div>
                <div>
                    <label class="pw-label">Message</label>
                    <textarea name="message" class="pw-input" rows="5" required>{{ old('message') }}</textarea>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="pw-label">Item ID</label>
                        <input name="item_id" type="number" value="{{ old('item_id') }}" class="pw-input">
                    </div>
                    <div>
                        <label class="pw-label">Count</label>
                        <input name="item_count" type="number" value="{{ old('item_count', 0) }}" class="pw-input">
                    </div>
                    <div>
                        <label class="pw-label">Gold</label>
                        <input name="money" type="number" value="{{ old('money', 0) }}" class="pw-input">
                    </div>
                </div>
                <button class="pw-btn pw-btn-primary w-full">Send</button>
            </form>
        </div>
    </div>

    <div class="pw-card lg:col-span-2">
        <div class="pw-card-title">History</div>
        <div class="pw-card-body !p-0">
            <table class="pw-table">
                <thead><tr><th>Recipient</th><th>Subject</th><th>Sent</th></tr></thead>
                <tbody>
                @forelse ($history as $h)
                    <tr>
                        <td class="font-medium">{{ $h->recipient_name }}</td>
                        <td>
                            {{ $h->title }}
                            <div class="text-xs text-[var(--color-text-muted)] truncate max-w-sm">{{ $h->message }}</div>
                        </td>
                        <td class="text-xs text-[var(--color-text-muted)] whitespace-nowrap">{{ $h->created_at }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center py-8 text-[var(--color-text-muted)]">Belum ada mail terkirim.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
