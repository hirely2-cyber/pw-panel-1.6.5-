<?php $__env->startSection('title', 'GM Users'); ?>
<?php $__env->startSection('breadcrumb', 'System Settings / GM Users'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('panel._flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    <div class="pw-card">
        <div class="pw-card-title">Add GM User</div>
        <div class="pw-card-body">
            <form method="POST" action="<?php echo e(route('panel.gm.store')); ?>" class="space-y-3">
                <?php echo csrf_field(); ?>
                <div><label class="pw-label">Username</label><input name="username" class="pw-input" required></div>
                <div><label class="pw-label">Email</label><input name="email" type="email" class="pw-input" required></div>
                <div><label class="pw-label">Display Name</label><input name="display_name" class="pw-input"></div>
                <div><label class="pw-label">Password (min 8)</label><input name="password" type="password" class="pw-input" required></div>
                <div>
                    <label class="pw-label">Role</label>
                    <select name="gm_role_id" class="pw-select" required>
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($r->id); ?>"><?php echo e($r->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" checked> Active
                </label>
                <button class="pw-btn pw-btn-primary w-full">Create</button>
            </form>
        </div>
    </div>

    <div class="pw-card lg:col-span-2">
        <div class="pw-card-title">GM Users (<?php echo e($users->total()); ?>)</div>
        <div class="pw-card-body !p-0">
            <div class="overflow-x-auto">
                <table class="pw-table">
                    <thead><tr><th>User</th><th>Role</th><th>Last Login</th><th class="text-right">Action</th></tr></thead>
                    <tbody>
                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u2): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <div class="font-medium"><?php echo e($u2->username); ?></div>
                                <div class="text-xs text-[var(--color-text-muted)]"><?php echo e($u2->email); ?></div>
                            </td>
                            <td>
                                <span class="pw-badge" style="background: <?php echo e($u2->role->color ?? '#eee'); ?>22; color: <?php echo e($u2->role->color ?? '#666'); ?>;">
                                    <?php echo e($u2->role->name ?? 'none'); ?>

                                </span>
                                <?php if(! $u2->is_active): ?><span class="pw-badge pw-badge-off ml-1">disabled</span><?php endif; ?>
                            </td>
                            <td class="text-xs text-[var(--color-text-muted)]">
                                <?php echo e($u2->last_login_at?->diffForHumans() ?? 'never'); ?>

                                <div class="font-mono"><?php echo e($u2->last_login_ip); ?></div>
                            </td>
                            <td class="text-right">
                                <?php if($u2->id !== auth('panel')->id()): ?>
                                    <form method="POST" action="<?php echo e(route('panel.gm.destroy', $u2)); ?>" class="inline" onsubmit="return confirm('Hapus user <?php echo e($u2->username); ?>?')">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button class="pw-btn pw-btn-sm pw-btn-danger">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-xs text-[var(--color-text-muted)]">(you)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="pw-card-body"><?php echo e($users->links()); ?></div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('panel.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/pw-panel/resources/views/panel/gm/index.blade.php ENDPATH**/ ?>