<?php $__env->startSection('title', 'Roles & Permissions'); ?>
<?php $__env->startSection('breadcrumb', 'System Settings / Roles & Perms'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('panel._flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="space-y-4">
<?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <form method="POST" action="<?php echo e(route('panel.gm.roles.update', $role)); ?>" class="pw-card">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

        <div class="pw-card-title"><?php echo e($role->name); ?> &nbsp;<span class="text-xs text-[var(--color-text-muted)] font-normal">(<?php echo e($role->users_count); ?> users)</span></div>

        <div class="pw-card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                <div><label class="pw-label">Name</label><input name="name" value="<?php echo e($role->name); ?>" class="pw-input"></div>
                <div>
                    <label class="pw-label">Color</label>
                    <div class="flex gap-2 items-center">
                        <input name="color" value="<?php echo e($role->color); ?>" class="pw-input font-mono">
                        <div class="w-9 h-9 rounded border border-[var(--color-border)]" style="background: <?php echo e($role->color); ?>"></div>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm self-end pb-2">
                    <input type="checkbox" name="is_super" value="1" <?php if($role->is_super): echo 'checked'; endif; ?>> Super Admin
                </label>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $perms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div>
                    <h4 class="text-xs uppercase tracking-wider text-[var(--color-accent)] mb-2 font-semibold"><?php echo e($group); ?></h4>
                    <div class="space-y-1">
                    <?php $__currentLoopData = $perms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $has = $role->permissions->contains($p->id); ?>
                        <label class="flex items-start gap-2 text-sm p-1.5 rounded hover:bg-[var(--color-surface-2)]">
                            <input type="checkbox" name="permissions[]" value="<?php echo e($p->id); ?>" <?php if($has): echo 'checked'; endif; ?> <?php if($role->is_super): echo 'disabled'; endif; ?> class="mt-0.5">
                            <span>
                                <span class="text-[var(--color-text)]"><?php echo e($p->label); ?></span>
                                <span class="block text-[10px] text-[var(--color-text-muted)] font-mono"><?php echo e($p->name); ?></span>
                            </span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="mt-4 text-right">
                <button class="pw-btn pw-btn-primary">Save <?php echo e($role->name); ?></button>
            </div>
        </div>
    </form>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('panel.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/pw-panel/resources/views/panel/gm/roles.blade.php ENDPATH**/ ?>