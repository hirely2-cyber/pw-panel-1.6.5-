@extends('panel.layouts.app')
@section('title', 'Profile')
@section('breadcrumb', 'My Profile')

@section('content')
@include('panel._flash')

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    <form method="POST" action="{{ route('panel.profile.update') }}" class="pw-card">
        @csrf @method('PUT')
        <div class="pw-card-title">Account Information</div>
        <div class="pw-card-body space-y-3">
            <div><label class="pw-label">Username</label><input value="{{ $user->username }}" class="pw-input" disabled></div>
            <div><label class="pw-label">Display Name</label><input name="display_name" value="{{ old('display_name', $user->display_name) }}" class="pw-input"></div>
            <div><label class="pw-label">Email</label><input name="email" type="email" value="{{ old('email', $user->email) }}" class="pw-input" required></div>
            <div>
                <label class="pw-label">Role</label>
                <div class="pw-input" style="color: {{ $user->role->color ?? '#666' }}; border-color: {{ $user->role->color ?? '#ccc' }};">
                    {{ $user->role->name ?? 'none' }}
                </div>
            </div>
            <button class="pw-btn pw-btn-primary">Save</button>
        </div>
    </form>

    <form method="POST" action="{{ route('panel.profile.password') }}" class="pw-card">
        @csrf @method('PUT')
        <div class="pw-card-title">Change Password</div>
        <div class="pw-card-body space-y-3">
            <div><label class="pw-label">Current Password</label><input name="current" type="password" class="pw-input" required></div>
            <div><label class="pw-label">New Password (min 8)</label><input name="password" type="password" class="pw-input" required></div>
            <div><label class="pw-label">Confirm New Password</label><input name="password_confirmation" type="password" class="pw-input" required></div>
            <button class="pw-btn pw-btn-accent">Change Password</button>

            @if ($user->last_login_at)
                <div class="pt-3 mt-2 border-t border-[var(--color-border)] text-xs text-[var(--color-text-muted)]">
                    Last login {{ $user->last_login_at->diffForHumans() }} dari
                    <code>{{ $user->last_login_ip }}</code>
                </div>
            @endif
        </div>
    </form>

</div>
@endsection
