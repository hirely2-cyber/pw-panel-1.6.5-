<?php $__env->startSection('title', 'Account #'.$account->ID); ?>
<?php $__env->startSection('breadcrumb', 'Accounts / '.$account->name); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('panel._flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    <div class="pw-card lg:col-span-2">
        <div class="pw-card-title">Account: <?php echo e($account->name); ?></div>
        <div class="pw-card-body">
            <table class="pw-table">
                <tbody>
                    <tr><td class="!w-40 text-[var(--color-text-muted)]">ID</td><td class="font-mono"><?php echo e($account->ID); ?></td></tr>
                    <tr><td class="text-[var(--color-text-muted)]">Username</td><td><?php echo e($account->name); ?></td></tr>
                    <tr><td class="text-[var(--color-text-muted)]">Email</td><td><?php echo e($account->email ?: '—'); ?></td></tr>
                    <tr><td class="text-[var(--color-text-muted)]">True Name</td><td><?php echo e($account->truename ?: '—'); ?></td></tr>
                    <tr><td class="text-[var(--color-text-muted)]">Gender</td><td><?php echo e($account->gender ?? '—'); ?></td></tr>
                    <tr><td class="text-[var(--color-text-muted)]">Created</td><td><?php echo e($account->creatime); ?></td></tr>
                    <tr><td class="text-[var(--color-text-muted)]">GM Rights</td><td><?php echo e($gmRight ? "zone {$gmRight->zoneid} / rid {$gmRight->rid}" : 'none'); ?></td></tr>
                </tbody>
            </table>
        </div>

        <div class="pw-card-title">Characters (<?php echo e($chars->count()); ?>)</div>
        <div class="pw-card-body !p-0">
            <table class="pw-table">
                <thead><tr><th>ID</th><th>Name</th><th>Race/Cls</th><th>Level</th><th class="text-right">Action</th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $chars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="font-mono text-xs"><?php echo e($c->id); ?></td>
                        <td class="font-medium"><?php echo e($c->name); ?></td>
                        <td class="text-xs text-[var(--color-text-muted)]"><?php echo e($c->race); ?> / <?php echo e($c->cls); ?></td>
                        <td><?php echo e($c->level ?? '—'); ?></td>
                        <td class="text-right"><a href="<?php echo e(route('panel.characters.show', $c->id)); ?>" class="pw-btn pw-btn-sm pw-btn-info">View</a></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center py-6 text-[var(--color-text-muted)]">Tidak ada karakter.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-4">
        
        <div class="pw-card">
            <div class="pw-card-title">Add Cubi Coin</div>
            <div class="pw-card-body">
                <form method="POST" action="<?php echo e(route('panel.accounts.cubi', $account->ID)); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
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

        
        <div class="pw-card">
            <div class="pw-card-title">Reset Password</div>
            <div class="pw-card-body">
                <form method="POST" action="<?php echo e(route('panel.accounts.password', $account->ID)); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="pw-label">Password baru</label>
                        <input type="text" name="password" class="pw-input" required>
                    </div>
                    <button class="pw-btn pw-btn-accent w-full">Update Password</button>
                    <p class="text-[11px] text-[var(--color-text-muted)]">
                        Hash mode: <code><?php echo e(\App\Services\PW\Server::current()->password_hash); ?></code>
                    </p>
                </form>
            </div>
        </div>
    </div>

</div>

<div class="mt-3"><a href="<?php echo e(route('panel.accounts.index')); ?>" class="pw-btn">← Kembali ke list</a></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('panel.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/pw-panel/resources/views/panel/account/show.blade.php ENDPATH**/ ?>