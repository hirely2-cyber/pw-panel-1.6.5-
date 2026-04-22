<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — PW Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('panel.css') }}?v=5">
</head>
<body>
@php
    $u = auth('panel')->user();

    /* Sidebar structure — mirroring iweb */
    $sidebar = [
        [
            'label' => 'Account Management',
            'icon'  => 'accounts',
            'items' => [
                ['accounts.index',   'Account List',   'account.view'],
            ],
        ],
        [
            'label' => 'Character Management',
            'icon'  => 'characters',
            'items' => [
                ['characters.index', 'All Characters', 'character.view'],
            ],
        ],
        [
            'label' => 'Server Control',
            'icon'  => 'server',
            'items' => [
                ['server.index',     'Process & Status', 'server.view'],
                ['settings.index',   'Server Settings',  'panel.settings'],
            ],
        ],
        [
            'label' => 'Messaging',
            'icon'  => 'mail',
            'items' => [
                ['mail.index',       'Send Mail',       'mail.send'],
                ['chat.index',       'Chat Log',        'chat.read'],
            ],
        ],
        [
            'label' => 'System Settings',
            'icon'  => 'settings',
            'items' => [
                ['gm.index',         'GM Users',        'panel.users'],
                ['gm.roles',         'Roles & Perms',   'panel.users'],
                ['logs.index',       'Audit Log',       'panel.logs'],
                ['profile.index',    'My Profile',      null],
            ],
        ],
    ];

    /* Determine which group is open based on current route */
    $currentRoute = request()->route()?->getName() ?? '';
    $openGroup = null;
    foreach ($sidebar as $gi => $g) {
        foreach ($g['items'] as $it) {
            if ('panel.'.$it[0] === $currentRoute) { $openGroup = $gi; break 2; }
        }
    }
@endphp

<div x-data="{ open: false, groups: { {{ $openGroup !== null ? "$openGroup: true" : '' }} } }" class="flex min-h-screen">

    {{-- =============== SIDEBAR =============== --}}
    <aside class="pw-sidebar fixed lg:static inset-y-0 left-0 z-40 w-60 border-r border-[var(--color-border)]
                  transform -translate-x-full lg:translate-x-0 transition-transform duration-200"
           :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        <div class="pw-sidebar-head flex items-center justify-between">
            <a href="{{ route('panel.dashboard') }}" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded flex items-center justify-center text-white text-xs font-bold"
                     style="background: linear-gradient(135deg, var(--color-accent), #e55b0e);">PW</div>
                <div>
                    <div class="text-sm font-semibold">Perfect World</div>
                    <div class="text-[10px] text-[var(--color-text-muted)] uppercase tracking-wider">Admin Panel</div>
                </div>
            </a>
            <button class="lg:hidden p-1 text-[var(--color-text-muted)]" @click="open = false">
                @include('panel._icon', ['name' => 'close', 'size' => 18])
            </button>
        </div>

        {{-- Dashboard (standalone) --}}
        <a href="{{ route('panel.dashboard') }}"
           class="pw-sidebar-group-head {{ request()->routeIs('panel.dashboard') ? 'text-[var(--color-primary)] bg-[var(--color-sidebar-active-bg)]' : '' }}">
            @include('panel._icon', ['name' => 'dashboard', 'size' => 16])
            <span>Dashboard</span>
        </a>

        @foreach ($sidebar as $gi => $group)
            @php
                $visibleItems = array_filter($group['items'], fn($it) => ! $it[2] || $u->can($it[2]));
            @endphp
            @if (count($visibleItems))
                <div class="pw-sidebar-group">
                    <button type="button"
                            class="pw-sidebar-group-head w-full"
                            :class="groups[{{ $gi }}] ? 'open' : ''"
                            @click="groups[{{ $gi }}] = !groups[{{ $gi }}]">
                        @include('panel._icon', ['name' => $group['icon'], 'size' => 16])
                        <span>{{ $group['label'] }}</span>
                        <span class="chev">@include('panel._icon', ['name' => 'chev-down', 'size' => 14])</span>
                    </button>
                    <div class="pw-sidebar-group-body" x-show="groups[{{ $gi }}]" x-cloak>
                        @foreach ($visibleItems as [$route, $label, $perm])
                            <a href="{{ route('panel.'.$route) }}"
                               class="pw-sidebar-item {{ request()->routeIs('panel.'.$route) ? 'active' : '' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </aside>

    {{-- Mobile backdrop --}}
    <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

    {{-- =============== MAIN =============== --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Top Header --}}
        <header class="h-12 flex items-center justify-between px-3 lg:px-4 bg-white border-b border-[var(--color-border)] shadow-sm">
            <div class="flex items-center gap-2">
                <button class="lg:hidden p-1.5 text-[var(--color-text-soft)]" @click="open = true">
                    @include('panel._icon', ['name' => 'menu', 'size' => 20])
                </button>
                <div class="text-[var(--color-text-soft)] text-sm font-medium">
                    @yield('breadcrumb', 'Home')
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div class="hidden sm:block text-right text-xs leading-tight mr-1">
                    <div class="text-[var(--color-text)] font-medium">{{ $u->display_name ?? $u->username }}</div>
                    <div class="text-[var(--color-text-muted)]">{{ $u->role?->name ?? '—' }}</div>
                </div>
                <a href="{{ route('panel.profile.index') }}" class="pw-btn pw-btn-sm">
                    @include('panel._icon', ['name' => 'user', 'size' => 14])
                    <span class="hidden md:inline">Profile</span>
                </a>
                <form method="POST" action="{{ route('panel.logout') }}">
                    @csrf
                    <button type="submit" class="pw-btn pw-btn-sm">Logout</button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-3 lg:p-5 max-w-full overflow-x-hidden">
            @yield('content')
        </main>
    </div>
</div>

@include('panel._toasts')
@include('panel._confirm-modal')

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
