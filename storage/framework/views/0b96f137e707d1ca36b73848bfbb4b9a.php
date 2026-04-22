<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Admin'); ?> — PW Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo e(asset('panel.css')); ?>?v=5">
</head>
<body>
<?php
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
?>

<div x-data="{ open: false, groups: { <?php echo e($openGroup !== null ? "$openGroup: true" : ''); ?> } }" class="flex min-h-screen">

    
    <aside class="pw-sidebar fixed lg:static inset-y-0 left-0 z-40 w-60 border-r border-[var(--color-border)]
                  transform -translate-x-full lg:translate-x-0 transition-transform duration-200"
           :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        <div class="pw-sidebar-head flex items-center justify-between">
            <a href="<?php echo e(route('panel.dashboard')); ?>" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded flex items-center justify-center text-white text-xs font-bold"
                     style="background: linear-gradient(135deg, var(--color-accent), #e55b0e);">PW</div>
                <div>
                    <div class="text-sm font-semibold">Perfect World</div>
                    <div class="text-[10px] text-[var(--color-text-muted)] uppercase tracking-wider">Admin Panel</div>
                </div>
            </a>
            <button class="lg:hidden p-1 text-[var(--color-text-muted)]" @click="open = false">
                <?php echo $__env->make('panel._icon', ['name' => 'close', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </button>
        </div>

        
        <a href="<?php echo e(route('panel.dashboard')); ?>"
           class="pw-sidebar-group-head <?php echo e(request()->routeIs('panel.dashboard') ? 'text-[var(--color-primary)] bg-[var(--color-sidebar-active-bg)]' : ''); ?>">
            <?php echo $__env->make('panel._icon', ['name' => 'dashboard', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <span>Dashboard</span>
        </a>

        <?php $__currentLoopData = $sidebar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gi => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $visibleItems = array_filter($group['items'], fn($it) => ! $it[2] || $u->can($it[2]));
            ?>
            <?php if(count($visibleItems)): ?>
                <div class="pw-sidebar-group">
                    <button type="button"
                            class="pw-sidebar-group-head w-full"
                            :class="groups[<?php echo e($gi); ?>] ? 'open' : ''"
                            @click="groups[<?php echo e($gi); ?>] = !groups[<?php echo e($gi); ?>]">
                        <?php echo $__env->make('panel._icon', ['name' => $group['icon'], 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <span><?php echo e($group['label']); ?></span>
                        <span class="chev"><?php echo $__env->make('panel._icon', ['name' => 'chev-down', 'size' => 14], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
                    </button>
                    <div class="pw-sidebar-group-body" x-show="groups[<?php echo e($gi); ?>]" x-cloak>
                        <?php $__currentLoopData = $visibleItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$route, $label, $perm]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('panel.'.$route)); ?>"
                               class="pw-sidebar-item <?php echo e(request()->routeIs('panel.'.$route) ? 'active' : ''); ?>">
                                <?php echo e($label); ?>

                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </aside>

    
    <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

    
    <div class="flex-1 flex flex-col min-w-0">

        
        <header class="h-12 flex items-center justify-between px-3 lg:px-4 bg-white border-b border-[var(--color-border)] shadow-sm">
            <div class="flex items-center gap-2">
                <button class="lg:hidden p-1.5 text-[var(--color-text-soft)]" @click="open = true">
                    <?php echo $__env->make('panel._icon', ['name' => 'menu', 'size' => 20], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </button>
                <div class="text-[var(--color-text-soft)] text-sm font-medium">
                    <?php echo $__env->yieldContent('breadcrumb', 'Home'); ?>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div class="hidden sm:block text-right text-xs leading-tight mr-1">
                    <div class="text-[var(--color-text)] font-medium"><?php echo e($u->display_name ?? $u->username); ?></div>
                    <div class="text-[var(--color-text-muted)]"><?php echo e($u->role?->name ?? '—'); ?></div>
                </div>
                <a href="<?php echo e(route('panel.profile.index')); ?>" class="pw-btn pw-btn-sm">
                    <?php echo $__env->make('panel._icon', ['name' => 'user', 'size' => 14], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <span class="hidden md:inline">Profile</span>
                </a>
                <form method="POST" action="<?php echo e(route('panel.logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="pw-btn pw-btn-sm">Logout</button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-3 lg:p-5 max-w-full overflow-x-hidden">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>
</div>

<?php echo $__env->make('panel._toasts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('panel._confirm-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
<?php /**PATH /var/www/html/pw-panel/resources/views/panel/layouts/app.blade.php ENDPATH**/ ?>