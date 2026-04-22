<?php $__env->startSection('title', 'Settings'); ?>
<?php $__env->startSection('breadcrumb', 'Server Control / Server Settings'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('panel._flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="pw-card">
    <div class="pw-card-title">Game Server Profiles</div>
    <div class="pw-card-body">
        <div class="text-xs text-[var(--color-text-muted)] mb-3">
            Default aktif: <strong class="text-[var(--color-accent)]"><?php echo e($current->name); ?></strong>
        </div>

        <div class="space-y-2">
            <?php $__currentLoopData = $servers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex flex-wrap items-center justify-between gap-3 p-3 border border-[var(--color-border)] rounded <?php echo e($s->is_default ? '!border-[var(--color-accent)] bg-[var(--color-accent-soft)]' : ''); ?>">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold"><?php echo e($s->name); ?></span>
                            <?php if($s->is_default): ?><span class="pw-badge pw-badge-warn">Default</span><?php endif; ?>
                            <?php if(! $s->is_active): ?><span class="pw-badge pw-badge-off">Inactive</span><?php endif; ?>
                        </div>
                        <div class="text-xs text-[var(--color-text-muted)] mt-1">
                            v<?php echo e($s->version); ?> · <?php echo e($s->socket_host); ?>:<?php echo e($s->socket_port); ?> · DB <?php echo e($s->db_name); ?>{{ $s->db_host }}
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <?php if(! $s->is_default): ?>
                            <form method="POST" action="<?php echo e(route('panel.settings.default', $s)); ?>">
                                <?php echo csrf_field(); ?>
                                <button class="pw-btn pw-btn-sm">Set Default</button>
                            </form>
                        <?php endif; ?>
                        <a href="<?php echo e(route('panel.settings.edit', $s)); ?>" class="pw-btn pw-btn-sm pw-btn-primary">Edit</a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('panel.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/pw-panel/resources/views/panel/settings/index.blade.php ENDPATH**/ ?>