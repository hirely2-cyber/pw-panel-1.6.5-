@extends('panel.layouts.app')
@section('title', 'Accounts')
@section('breadcrumb', 'Account Management / Account List')

@section('content')
@include('panel._flash')

<div class="pw-card mb-4">
    <div class="pw-card-title">Search Accounts</div>
    <div class="pw-card-body">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="pw-label">Username / Email / ID</label>
                <input type="text" name="q" value="{{ $q }}" class="pw-input" placeholder="Cari...">
            </div>
            <div>
                <button class="pw-btn pw-btn-primary">
                    @include('panel._icon', ['name' => 'search', 'size' => 14])
                    Search
                </button>
            </div>
            @if ($q)
                <div><a href="{{ route('panel.accounts.index') }}" class="pw-btn">Reset</a></div>
            @endif
        </form>
    </div>
</div>

<div class="pw-card">
    <div class="pw-card-title">Accounts ({{ $accounts->total() }})</div>
    <div class="pw-card-body !p-0">
        <div class="overflow-x-auto">
            <table class="pw-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Created</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($accounts as $a)
                    <tr>
                        <td class="font-mono text-xs">{{ $a->ID }}</td>
                        <td class="font-medium">{{ $a->name }}</td>
                        <td class="text-[var(--color-text-soft)]">{{ $a->email ?: '—' }}</td>
                        <td class="text-xs text-[var(--color-text-muted)]">{{ $a->creatime }}</td>
                        <td class="text-right">
                            <a href="{{ route('panel.accounts.show', $a->ID) }}" class="pw-btn pw-btn-sm pw-btn-info">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-8 text-[var(--color-text-muted)]">Tidak ada akun.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">{{ $accounts->links() }}</div>
@endsection
