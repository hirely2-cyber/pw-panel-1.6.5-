<?php $__env->startSection('title', 'Audit Log'); ?>
<?php $__env->startSection('breadcrumb', 'System Settings / Audit Log'); ?>

<?php $__env->startSection('content'); ?>
<div class="pw-card">
    <div class="pw-card-title">Audit Log</div>
    <div class="pw-card-body !p-0">
        <div class="overflow-x-auto">
            <table class="pw-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>GM</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>IP</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="text-xs text-[var(--color-text-muted)] whitespace-nowrap"><?php echo e($l->created_at); ?></td>
                        <td class="font-medium"><?php echo e($l->user->username ?? '—'); ?></td>
                        <td><code><?php echo e($l->action); ?></code></td>
                        <td class="text-xs text-[var(--color-text-muted)]"><?php echo e($l->target_type); ?> <?php echo e($l->target_id); ?></td>
                        <td class="font-mono text-xs"><?php echo e($l->ip); ?></td>
                        <td>
                            <?php if($l->success): ?><span class="pw-badge pw-badge-ok">OK</span>
                            <?php else: ?><span class="pw-badge pw-badge-off">FAIL</span><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="text-center py-8 text-[var(--color-text-muted)]">Belum ada log.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="pw-card-body"><?php echo e($logs->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('panel.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/pw-panel/resources/views/panel/logs/index.blade.php ENDPATH**/ ?>