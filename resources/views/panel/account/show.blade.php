@extends('panel.layouts.app')
@section('title', 'Account #'.$account->ID)
@section('breadcrumb', 'Accounts / '.$account->name)

@section('content')
@include('panel._flash')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    <div class="pw-card lg:col-span-2">
        <div class="pw-card-title">Account: {{ $account->name }}</div>
        <div class="pw-card-body">
            <table class="pw-table">
                <tbody>
                    <tr><td class="!w-40 text-[var(--color-text-muted)]">ID</td><td class="font-mono">{{ $account->ID }}</td></tr>
                    <tr><td class="text-[var(--color-text-muted)]">Username</td><td>{{ $account->name }}</td></tr>
                    <tr><td class="text-[var(--color-text-muted)]">Email</td><td>{{ $account->email ?: '—' }}</td></tr>
                    <tr><td class="text-[var(--color-text-muted)]">True Name</td><td>{{ $account->truename ?: '—' }}</td></tr>
                    <tr><td class="text-[var(--color-text-muted)]">Gender</td><td>{{ $account->gender ?? '—' }}</td></tr>
                    <tr><td class="text-[var(--color-text-muted)]">Created</td><td>{{ $account->creatime }}</td></tr>
                    <tr><td class="text-[var(--color-text-muted)]">GM Rights</td><td>{{ $gmRight ? "zone {$gmRight->zoneid} / rid {$gmRight->rid}" : 'none' }}</td></tr>
                </tbody>
            </table>
        </div>

        <div class="pw-card-title">Characters ({{ $chars->count() }})</div>
        <div class="pw-card-body !p-0">
            <table class="pw-table">
                <thead><tr><th>ID</th><th>Name</th><th>Race/Cls</th><th>Level</th><th class="text-right">Action</th></tr></thead>
                <tbody>
                @forelse ($chars as $c)
                    <tr>
                        <td class="font-mono text-xs">{{ $c->id }}</td>
                        <td class="font-medium">{{ $c->name }}</td>
                        <td class="text-xs text-[var(--color-text-muted)]">{{ $c->race }} / {{ $c->cls }}</td>
                        <td>{{ $c->level ?? '—' }}</td>
                        <td class="text-right"><a href="{{ route('panel.characters.show', $c->id) }}" class="pw-btn pw-btn-sm pw-btn-info">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-6 text-[var(--color-text-muted)]">Tidak ada karakter.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-4">
        {{-- Add Cubi --}}
        <div class="pw-card">
            <div class="pw-card-title">Add Cubi Coin</div>
            <div class="pw-card-body">
                <form method="POST" action="{{ route('panel.accounts.cubi', $account->ID) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="pw-label">Jumlah Cubi</label>
                        <input type="number" name="amount" class="pw-input" min="1" max="9999999"
                               placeholder="contoh: 1000" required>
                    </div>
                    <button class="pw-btn pw-btn-accent w-full">+ Add Cubi</button>
                    <p class="text-[11px] text-[var(--color-text-muted)]">
                        Membutuhkan game server menyala. Cubi langsung masuk ke akun.
                    </p>
                </form>
            </div>
        </div>

        {{-- Reset Password --}}
        <div class="pw-card">
            <div class="pw-card-title">Reset Password</div>
            <div class="pw-card-body">
                <form method="POST" action="{{ route('panel.accounts.password', $account->ID) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="pw-label">Password baru</label>
                        <input type="text" name="password" class="pw-input" required>
                    </div>
                    <button class="pw-btn pw-btn-accent w-full">Update Password</button>
                    <p class="text-[11px] text-[var(--color-text-muted)]">
                        Hash mode: <code>{{ \App\Services\PW\Server::current()->password_hash }}</code>
                    </p>
                </form>
            </div>
        </div>
    </div>

</div>

<div class="mt-3"><a href="{{ route('panel.accounts.index') }}" class="pw-btn">← Kembali ke list</a></div>
@endsection
