<?php $__env->startSection('title', 'Profile'); ?>
<?php $__env->startSection('breadcrumb', 'My Profile'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('panel._flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    <form method="POST" action="<?php echo e(route('panel.profile.update')); ?>" class="pw-card">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="pw-card-title">Account Information</div>
        <div class="pw-card-body space-y-3">
            <div><label class="pw-label">Username</label><input value="<?php echo e($user->username); ?>" class="pw-input" disabled></div>
            <div><label class="pw-label">Display Name</label><input name="display_name" value="<?php echo e(old('display_name', $user->display_name)); ?>" class="pw-input"></div>
            <div><label class="pw-label">Email</label><input name="email" type="email" value="<?php echo e(old('email', $user->email)); ?>" class="pw-input" required></div>
            <div>
                <label class="pw-label">Role</label>
                <div class="pw-input" style="color: <?php echo e($user->role->color ?? '#666'); ?>; border-color: <?php echo e($user->role->color ?? '#ccc'); ?>;">
                    <?php echo e($user->role->name ?? 'none'); ?>

                </div>
            </div>
            <button class="pw-btn pw-btn-primary">Save</button>
        </div>
    </form>

    <form method="POST" action="<?php echo e(route('panel.profile.password')); ?>" class="pw-card">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div class="pw-card-title">Change Password</div>
        <div class="pw-card-body space-y-3">
            <div><label class="pw-label">Current Password</label><input name="current" type="password" class="pw-input" required></div>
            <div><label class="pw-label">New Password (min 8)</label><input name="password" type="password" class="pw-input" required></div>
            <div><label class="pw-label">Confirm New Password</label><input name="password_confirmation" type="password" class="pw-input" required></div>
            <button class="pw-btn pw-btn-accent">Change Password</button>

            <?php if($user->last_login_at): ?>
                <div class="pt-3 mt-2 border-t border-[var(--color-border)] text-xs text-[var(--color-text-muted)]">
                    Last login <?php echo e($user->last_login_at->diffForHumans()); ?> dari
                    <code><?php echo e($user->last_login_ip); ?></code>
                </div>
            <?php endif; ?>
        </div>
    </form>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('panel.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/pw-panel/resources/views/panel/profile/index.blade.php ENDPATH**/ ?>