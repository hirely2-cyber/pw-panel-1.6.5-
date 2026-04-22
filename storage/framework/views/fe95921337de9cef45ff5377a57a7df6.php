<?php $__env->startSection('title', 'Accounts'); ?>
<?php $__env->startSection('breadcrumb', 'Account Management / Account List'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('panel._flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="pw-card mb-4">
    <div class="pw-card-title">Search Accounts</div>
    <div class="pw-card-body">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="pw-label">Username / Email / ID</label>
                <input type="text" name="q" value="<?php echo e($q); ?>" class="pw-input" placeholder="Cari...">
            </div>
            <div>
                <button class="pw-btn pw-btn-primary">
                    <?php echo $__env->make('panel._icon', ['name' => 'search', 'size' => 14], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    Search
                </button>
            </div>
            <?php if($q): ?>
                <div><a href="<?php echo e(route('panel.accounts.index')); ?>" class="pw-btn">Reset</a></div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="pw-card">
    <div class="pw-card-title">Accounts (<?php echo e($accounts->total()); ?>)</div>
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
                <?php $__empty_1 = true; $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="font-mono text-xs"><?php echo e($a->ID); ?></td>
                        <td class="font-medium"><?php echo e($a->name); ?></td>
                        <td class="text-[var(--color-text-soft)]"><?php echo e($a->email ?: '—'); ?></td>
                        <td class="text-xs text-[var(--color-text-muted)]"><?php echo e($a->creatime); ?></td>
                        <td class="text-right">
                            <a href="<?php echo e(route('panel.accounts.show', $a->ID)); ?>" class="pw-btn pw-btn-sm pw-btn-info">View</a>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="5" class="text-center py-8 text-[var(--color-text-muted)]">Tidak ada akun.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3"><?php echo e($accounts->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('panel.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/pw-panel/resources/views/panel/account/index.blade.php ENDPATH**/ ?>