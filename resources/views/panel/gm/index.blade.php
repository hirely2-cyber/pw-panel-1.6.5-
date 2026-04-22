@extends('panel.layouts.app')
@section('title', 'GM Users')
@section('breadcrumb', 'System Settings / GM Users')

@section('content')
@include('panel._flash')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    <div class="pw-card">
        <div class="pw-card-title">Add GM User</div>
        <div class="pw-card-body">
            <form method="POST" action="{{ route('panel.gm.store') }}" class="space-y-3">
                @csrf
                <div><label class="pw-label">Username</label><input name="username" class="pw-input" required></div>
                <div><label class="pw-label">Email</label><input name="email" type="email" class="pw-input" required></div>
                <div><label class="pw-label">Display Name</label><input name="display_name" class="pw-input"></div>
                <div><label class="pw-label">Password (min 8)</label><input name="password" type="password" class="pw-input" required></div>
                <div>
                    <label class="pw-label">Role</label>
                    <select name="gm_role_id" class="pw-select" required>
                        @foreach ($roles as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" checked> Active
                </label>
                <button class="pw-btn pw-btn-primary w-full">Create</button>
            </form>
        </div>
    </div>

    <div class="pw-card lg:col-span-2">
        <div class="pw-card-title">GM Users ({{ $users->total() }})</div>
        <div class="pw-card-body !p-0">
            <div class="overflow-x-auto">
                <table class="pw-table">
                    <thead><tr><th>User</th><th>Role</th><th>Last Login</th><th class="text-right">Action</th></tr></thead>
                    <tbody>
                    @foreach ($users as $u2)
                        <tr>
                            <td>
                                <div class="font-medium">{{ $u2->username }}</div>
                                <div class="text-xs text-[var(--color-text-muted)]">{{ $u2->email }}</div>
                            </td>
                            <td>
                                <span class="pw-badge" style="background: {{ $u2->role->color ?? '#eee' }}22; color: {{ $u2->role->color ?? '#666' }};">
                                    {{ $u2->role->name ?? 'none' }}
                                </span>
                                @if (! $u2->is_active)<span class="pw-badge pw-badge-off ml-1">disabled</span>@endif
                            </td>
                            <td class="text-xs text-[var(--color-text-muted)]">
                                {{ $u2->last_login_at?->diffForHumans() ?? 'never' }}
                                <div class="font-mono">{{ $u2->last_login_ip }}</div>
                            </td>
                            <td class="text-right">
                                @if ($u2->id !== auth('panel')->id())
                                    <form method="POST" action="{{ route('panel.gm.destroy', $u2) }}" class="inline" onsubmit="return confirm('Hapus user {{ $u2->username }}?')">
                                        @csrf @method('DELETE')
                                        <button class="pw-btn pw-btn-sm pw-btn-danger">Delete</button>
                                    </form>
                                @else
                                    <span class="text-xs text-[var(--color-text-muted)]">(you)</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pw-card-body">{{ $users->links() }}</div>
    </div>

</div>
@endsection
