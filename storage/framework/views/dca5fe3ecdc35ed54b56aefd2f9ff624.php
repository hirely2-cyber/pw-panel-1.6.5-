<?php $__env->startSection('title', 'Mail'); ?>
<?php $__env->startSection('breadcrumb', 'Messaging / Send Mail'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('panel._flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    <div class="pw-card">
        <div class="pw-card-title">Compose Mail</div>
        <div class="pw-card-body">
            <form method="POST" action="<?php echo e(route('panel.mail.send')); ?>" class="space-y-3">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="pw-label">Recipient (character name)</label>
                    <input name="recipient_name" value="<?php echo e(old('recipient_name')); ?>" class="pw-input" required>
                </div>
                <div>
                    <label class="pw-label">Subject</label>
                    <input name="title" value="<?php echo e(old('title')); ?>" class="pw-input" required>
                </div>
                <div>
                    <label class="pw-label">Message</label>
                    <textarea name="message" class="pw-input" rows="5" required><?php echo e(old('message')); ?></textarea>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="pw-label">Item ID</label>
                        <input name="item_id" type="number" value="<?php echo e(old('item_id')); ?>" class="pw-input">
                    </div>
                    <div>
                        <label class="pw-label">Count</label>
                        <input name="item_count" type="number" value="<?php echo e(old('item_count', 0)); ?>" class="pw-input">
                    </div>
                    <div>
                        <label class="pw-label">Gold</label>
                        <input name="money" type="number" value="<?php echo e(old('money', 0)); ?>" class="pw-input">
                    </div>
                </div>
                <button class="pw-btn pw-btn-primary w-full">Send</button>
            </form>
        </div>
    </div>

    <div class="pw-card lg:col-span-2">
        <div class="pw-card-title">History</div>
        <div class="pw-card-body !p-0">
            <table class="pw-table">
                <thead><tr><th>Recipient</th><th>Subject</th><th>Sent</th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="font-medium"><?php echo e($h->recipient_name); ?></td>
                        <td>
                            <?php echo e($h->title); ?>

                            <div class="text-xs text-[var(--color-text-muted)] truncate max-w-sm"><?php echo e($h->message); ?></div>
                        </td>
                        <td class="text-xs text-[var(--color-text-muted)] whitespace-nowrap"><?php echo e($h->created_at); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="3" class="text-center py-8 text-[var(--color-text-muted)]">Belum ada mail terkirim.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('panel.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/pw-panel/resources/views/panel/mail/index.blade.php ENDPATH**/ ?>