<?php $__env->startSection('title', 'Login'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex items-center justify-center px-4"
     style="background: linear-gradient(135deg, #2a4365 0%, #1a365d 50%, #2a4365 100%);">

    <div class="w-full max-w-sm">
        <div class="bg-white rounded-lg shadow-2xl overflow-hidden">

            
            <div class="relative h-24 flex items-center justify-center"
                 style="background: linear-gradient(135deg, #3a8bd4 0%, #2094f3 100%);">
                <div class="text-center">
                    <div class="text-white text-xl font-bold tracking-widest" style="text-shadow: 0 1px 2px rgba(0,0,0,.3)">PERFECT WORLD</div>
                    <div class="text-white/80 text-[10px] tracking-[0.3em] mt-0.5">ADMIN PANEL</div>
                </div>
            </div>

            <div class="p-6">
                <?php if($errors->any()): ?>
                    <div class="mb-4 p-3 border border-red-300 bg-red-50 rounded text-sm text-red-700">
                        <?php echo e($errors->first()); ?>

                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('panel.login')); ?>" class="space-y-4">
                    <?php echo csrf_field(); ?>

                    <div>
                        <label class="pw-label">Username</label>
                        <input type="text" name="username" value="<?php echo e(old('username')); ?>" class="pw-input" required autofocus placeholder="Enter your login account">
                    </div>

                    <div>
                        <label class="pw-label">Password</label>
                        <input type="password" name="password" class="pw-input" required placeholder="Enter password">
                    </div>

                    <label class="flex items-center gap-2 text-sm text-[var(--color-text-soft)]">
                        <input type="checkbox" name="remember"> Remember me
                    </label>

                    <button type="submit" class="pw-btn pw-btn-primary w-full !py-2.5">
                        Log in
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center text-xs text-white/60 mt-6">
            &copy; <?php echo e(date('Y')); ?> PW Admin Panel
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('panel.layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/pw-panel/resources/views/panel/auth/login.blade.php ENDPATH**/ ?>