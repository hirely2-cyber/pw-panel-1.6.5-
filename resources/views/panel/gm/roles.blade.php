@extends('panel.layouts.app')
@section('title', 'Roles & Permissions')
@section('breadcrumb', 'System Settings / Roles & Perms')

@section('content')
@include('panel._flash')

<div class="space-y-4">
@foreach ($roles as $role)
    <form method="POST" action="{{ route('panel.gm.roles.update', $role) }}" class="pw-card">
        @csrf @method('PUT')

        <div class="pw-card-title">{{ $role->name }} &nbsp;<span class="text-xs text-[var(--color-text-muted)] font-normal">({{ $role->users_count }} users)</span></div>

        <div class="pw-card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                <div><label class="pw-label">Name</label><input name="name" value="{{ $role->name }}" class="pw-input"></div>
                <div>
                    <label class="pw-label">Color</label>
                    <div class="flex gap-2 items-center">
                        <input name="color" value="{{ $role->color }}" class="pw-input font-mono">
                        <div class="w-9 h-9 rounded border border-[var(--color-border)]" style="background: {{ $role->color }}"></div>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm self-end pb-2">
                    <input type="checkbox" name="is_super" value="1" @checked($role->is_super)> Super Admin
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($permissions as $group => $perms)
                <div>
                    <h4 class="text-xs uppercase tracking-wider text-[var(--color-accent)] mb-2 font-semibold">{{ $group }}</h4>
                    <div class="space-y-1">
                    @foreach ($perms as $p)
                        @php $has = $role->permissions->contains($p->id); @endphp
                        <label class="flex items-start gap-2 text-sm p-1.5 rounded hover:bg-[var(--color-surface-2)]">
                            <input type="checkbox" name="permissions[]" value="{{ $p->id }}" @checked($has) @disabled($role->is_super) class="mt-0.5">
                            <span>
                                <span class="text-[var(--color-text)]">{{ $p->label }}</span>
                                <span class="block text-[10px] text-[var(--color-text-muted)] font-mono">{{ $p->name }}</span>
                            </span>
                        </label>
                    @endforeach
                    </div>
                </div>
            @endforeach
            </div>

            <div class="mt-4 text-right">
                <button class="pw-btn pw-btn-primary">Save {{ $role->name }}</button>
            </div>
        </div>
    </form>
@endforeach
</div>
@endsection
