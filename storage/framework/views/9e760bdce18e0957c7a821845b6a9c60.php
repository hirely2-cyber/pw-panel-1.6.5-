<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('breadcrumb', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('panel._server-info', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="space-y-5">

    
    <div class="pw-card">
        <div class="pw-card-title">Current Server</div>
        <div class="pw-card-body flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="text-lg font-semibold text-[var(--color-text)]"><?php echo e($server->name); ?></div>
                <div class="text-xs text-[var(--color-text-muted)] mt-0.5">
                    v<?php echo e($server->version); ?> &nbsp;·&nbsp; <?php echo e($server->socket_host); ?>:<?php echo e($server->socket_port); ?>

                </div>
            </div>
            <?php if($isOnline): ?>
                <span class="pw-badge pw-badge-ok">● Online</span>
            <?php else: ?>
                <span class="pw-badge pw-badge-off">● Offline</span>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="pw-card p-4">
            <div class="text-xs text-[var(--color-text-muted)] mb-1 flex items-center justify-between">
                <span>Accounts</span>
                <span class="pw-badge pw-badge-info">Real-time</span>
            </div>
            <div class="text-2xl font-semibold text-[var(--color-accent)]"><?php echo e(number_format($accountCount)); ?></div>
            <div class="text-[11px] text-[var(--color-text-muted)] mt-0.5">Total accounts in DB</div>
        </div>
        <div class="pw-card p-4">
            <div class="text-xs text-[var(--color-text-muted)] mb-1 flex items-center justify-between">
                <span>Characters</span>
                <span class="pw-badge pw-badge-info">Real-time</span>
            </div>
            <div class="text-2xl font-semibold text-[var(--color-accent)]"><?php echo e(number_format($charCount)); ?></div>
            <div class="text-[11px] text-[var(--color-text-muted)] mt-0.5">Total characters</div>
        </div>
        <div class="pw-card p-4">
            <div class="text-xs text-[var(--color-text-muted)] mb-1 flex items-center justify-between">
                <span>Memory</span>
                <span class="pw-badge pw-badge-muted"><?php echo e($memPct); ?>%</span>
            </div>
            <div class="text-2xl font-semibold text-[var(--color-text)]"><?php echo e($memUsed); ?><span class="text-sm text-[var(--color-text-muted)]"> / <?php echo e($memTotal); ?> MB</span></div>
            <div class="mt-2 h-1.5 bg-[var(--color-surface-2)] rounded-full overflow-hidden">
                <div class="h-full" style="width:<?php echo e($memPct); ?>%; background: var(--color-primary)"></div>
            </div>
        </div>
        <div class="pw-card p-4">
            <div class="text-xs text-[var(--color-text-muted)] mb-1 flex items-center justify-between">
                <span>Swap</span>
                <span class="pw-badge pw-badge-muted"><?php echo e($swapPct); ?>%</span>
            </div>
            <div class="text-2xl font-semibold text-[var(--color-text)]"><?php echo e($swapUsed); ?><span class="text-sm text-[var(--color-text-muted)]"> / <?php echo e($swapTotal); ?> MB</span></div>
            <div class="mt-2 h-1.5 bg-[var(--color-surface-2)] rounded-full overflow-hidden">
                <div class="h-full" style="width:<?php echo e($swapPct); ?>%; background: var(--color-danger)"></div>
            </div>
        </div>
    </div>

    
    <div class="pw-card">
        <div class="pw-card-title">Quick Access</div>
        <div class="pw-card-body grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <?php
                $quick = [
                    ['panel.accounts.index',   'Accounts',     'accounts',   'account.view'],
                    ['panel.characters.index', 'Characters',   'characters', 'character.view'],
                    ['panel.server.index',     'Server',       'server',     'server.view'],
                    ['panel.settings.index',   'Settings',     'settings',   'panel.settings'],
                    ['panel.mail.index',       'Mail',         'mail',       'mail.send'],
                    ['panel.chat.index',       'Chat Log',     'chat',       'chat.read'],
                ];
                $u = auth('panel')->user();
            ?>
            <?php $__currentLoopData = $quick; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$r, $l, $i, $p]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(! $p || $u->can($p)): ?>
                    <a href="<?php echo e(route($r)); ?>"
                       class="flex flex-col items-center justify-center gap-2 p-4 rounded border border-[var(--color-border)] hover:border-[var(--color-primary)] hover:text-[var(--color-primary)] transition">
                        <?php echo $__env->make('panel._icon', ['name' => $i, 'size' => 26], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <span class="text-xs font-medium"><?php echo e($l); ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        
        <div class="pw-card">
            <div class="pw-card-title">Server Configuration</div>
            <div class="pw-card-body">
                <table class="pw-table">
                    <tbody>
                        <tr><td class="!w-40 text-[var(--color-text-muted)]">Name</td><td><?php echo e($server->name); ?></td></tr>
                        <tr><td class="text-[var(--color-text-muted)]">Version</td><td><?php echo e($server->version); ?></td></tr>
                        <tr><td class="text-[var(--color-text-muted)]">Auth Service</td><td><?php echo e($server->auth_type); ?></td></tr>
                        <tr><td class="text-[var(--color-text-muted)]">Link Port</td><td><?php echo e($server->link_port); ?></td></tr>
                        <tr><td class="text-[var(--color-text-muted)]">Password Hash</td><td><?php echo e($server->password_hash); ?></td></tr>
                        <tr><td class="text-[var(--color-text-muted)]">Server Path</td><td><code><?php echo e($server->server_path); ?></code></td></tr>
                        <tr><td class="text-[var(--color-text-muted)]">Logs Path</td><td><code><?php echo e($server->logs_path); ?></code></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="pw-card">
            <div class="pw-card-title">Roadmap</div>
            <div class="pw-card-body text-sm text-[var(--color-text-soft)] space-y-1.5">
                <div>• Port <code>Stream.php</code> + <code>GRole.php</code> untuk character editor binary</div>
                <div>• Implementasi real socket dispatch pada Server Control</div>
                <div>• 2FA untuk akun GM</li>
                <div>• Integrasi ECharts untuk grafik distribusi kelas</div>
            </div>
        </div>
    </div>
</div>

<div class="mt-5">
    <?php echo $__env->make('panel._backup-list', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('panel.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/pw-panel/resources/views/panel/dashboard.blade.php ENDPATH**/ ?>