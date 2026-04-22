<?php $__env->startSection('title', 'Chat Log'); ?>
<?php $__env->startSection('breadcrumb', 'Messaging / Chat Log'); ?>

<?php $__env->startSection('content'); ?>
<div class="pw-card">
    <div class="pw-card-title">Recent Chat Messages</div>
    <div class="pw-card-body !p-0">
        <div class="overflow-x-auto">
            <table class="pw-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Channel</th>
                        <th>From</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="font-mono text-xs whitespace-nowrap"><?php echo e($l->time ?? ''); ?></td>
                        <td><span class="pw-badge pw-badge-info"><?php echo e($l->channel ?? '—'); ?></span></td>
                        <td class="font-medium"><?php echo e($l->srcroleName ?? $l->srcrole ?? '—'); ?></td>
                        <td><?php echo e($l->msg ?? $l->message ?? ''); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="text-center py-8 text-[var(--color-text-muted)]">
                        Tabel <code>log_chat</code> tidak ditemukan atau kosong.
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('panel.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/pw-panel/resources/views/panel/chat/index.blade.php ENDPATH**/ ?>