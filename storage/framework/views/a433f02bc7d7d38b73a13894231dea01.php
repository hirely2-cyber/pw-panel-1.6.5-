<?php $__env->startSection('title', 'Characters'); ?>
<?php $__env->startSection('breadcrumb', 'Character Management / All Characters'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('panel._flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<div class="pw-card mb-3">
    <div class="pw-card-title">
        <span class="inline-flex items-center gap-2">
            <span class="text-[var(--color-accent)]">⌕</span>
            SEARCH CHARACTER
        </span>
    </div>
    <div class="pw-card-body !py-3">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-2 items-end">
            <div>
                <label class="pw-label">Account ID</label>
                <input type="text" name="userid" value="<?php echo e($filters['userid'] ?? ''); ?>" class="pw-input" placeholder="1024">
            </div>
            <div>
                <label class="pw-label">Account Name</label>
                <input type="text" name="username" value="<?php echo e($filters['username'] ?? ''); ?>" class="pw-input" placeholder="pw176">
            </div>
            <div>
                <label class="pw-label">Role ID</label>
                <input type="text" name="role_id" value="<?php echo e($filters['role_id'] ?? ''); ?>" class="pw-input" placeholder="1024">
            </div>
            <div>
                <label class="pw-label">Role Name</label>
                <input type="text" name="role_name" value="<?php echo e($filters['role_name'] ?? ''); ?>" class="pw-input" placeholder="Hero">
            </div>
            <?php $anyFilter = collect($filters ?? [])->filter(fn($v) => $v !== '' && $v !== null)->isNotEmpty(); ?>
            <div class="flex items-center gap-1">
                <button class="pw-btn pw-btn-primary pw-btn-sm">
                    <?php echo $__env->make('panel._icon', ['name' => 'search', 'size' => 13], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    Search
                </button>
                <?php if($anyFilter): ?>
                    <a href="<?php echo e(route('panel.characters.index')); ?>" class="pw-btn pw-btn-sm">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>


<div class="pw-card">
    <div class="pw-card-title flex items-center justify-between">
        <span class="inline-flex items-center gap-2">
            <span class="text-[var(--color-accent)]">☰</span>
            CHARACTERS
            <span class="pw-badge pw-badge-ok !text-[10px] !py-0 !px-1.5 normal-case">
                <?php echo e(number_format($characters->total())); ?> total
            </span>
        </span>
        <span class="text-[11px] text-[var(--color-text-muted)] font-normal normal-case tracking-normal">
            Page <?php echo e($characters->currentPage()); ?> / <?php echo e($characters->lastPage()); ?>

        </span>
    </div>
    <div class="pw-card-body !p-0">
        <div class="overflow-x-auto">
            <table class="pw-table pw-table-compact">
                <thead>
                    <tr>
                        <th class="w-16">Role ID</th>
                        <th class="w-[150px]">Account</th>
                        <th>Name</th>
                        <th class="w-[170px]">Class / Race</th>
                        <th class="w-12 text-center">Lv</th>
                        <th class="w-[150px]">Faction</th>
                        <th class="w-16 text-center">Status</th>
                        <th class="text-right w-[210px]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $characters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="font-mono text-[11px] text-[var(--color-text-muted)]"><?php echo e($c->id); ?></td>

                        <td>
                            <div class="text-[12px] font-semibold leading-tight truncate"><?php echo e($c->username ?? '(deleted)'); ?></div>
                            <div class="text-[10px] text-[var(--color-text-muted)] font-mono leading-tight">uid <?php echo e($c->userid); ?></div>
                        </td>

                        <td>
                            <span class="text-[13px] font-semibold"><?php echo e($c->name); ?></span>
                        </td>

                        <td>
                            <?php echo $__env->make('panel._class-badge', [
                                'occupation' => $c->cls,
                                'gender'     => $c->gender,
                                'race'       => $c->race,
                                'size'       => 'sm',
                                'showName'   => true,
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </td>

                        <td class="text-center">
                            <span class="inline-flex items-center justify-center min-w-[2rem] px-1.5 py-0 rounded bg-slate-100 text-slate-700 text-[12px] font-bold tabular-nums">
                                <?php echo e($c->level ?? 0); ?>

                            </span>
                        </td>

                        <td>
                            <?php if(!empty($c->faction_name)): ?>
                                <div class="text-[12px] font-semibold leading-tight truncate"><?php echo e($c->faction_name); ?></div>
                                <div class="text-[10px] text-[var(--color-text-muted)] leading-tight">Lv <?php echo e($c->faction_level ?? 0); ?></div>
                            <?php else: ?>
                                <span class="text-[11px] text-[var(--color-text-muted)]">—</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-center">
                            <?php
                                $online = (bool) ($c->online ?? false);
                                $frozen = (bool) ($c->frozen ?? false);
                            ?>
                            <?php if($frozen): ?>
                                <span class="pw-badge pw-badge-warn !text-[10px] !py-0 !px-1.5">Frozen</span>
                            <?php elseif($online): ?>
                                <span class="pw-badge pw-badge-ok !text-[10px] !py-0 !px-1.5">Online</span>
                            <?php else: ?>
                                <span class="pw-badge pw-badge-off !text-[10px] !py-0 !px-1.5">Offline</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-right">
                            <div class="inline-flex items-center gap-1">
                                <a href="<?php echo e(route('panel.characters.show', $c->id)); ?>"
                                   class="pw-btn pw-btn-sm pw-btn-info" title="View details">View</a>
                                <button type="button" disabled class="pw-btn pw-btn-sm" title="Edit XML (coming)">XML</button>
                                <button type="button" disabled class="pw-btn pw-btn-sm pw-btn-warn" title="Rename (coming)">Rename</button>
                                <button type="button" disabled class="pw-btn pw-btn-sm pw-btn-danger" title="Delete (coming)">Del</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="text-center py-10 text-[var(--color-text-muted)]">
                            <div class="text-3xl mb-2">○</div>
                            No characters found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3"><?php echo e($characters->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('title', 'Characters'); ?>
<?php $__env->startSection('breadcrumb', 'Character Management / All Characters'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('panel._flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<div class="pw-card mb-4">
    <div class="pw-card-title">
        <span class="inline-flex items-center gap-2">
            <span class="text-[var(--color-accent)]">⌕</span>
            SEARCH CHARACTER
        </span>
    </div>
    <div class="pw-card-body">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div>
                <label class="pw-label">Account ID</label>
                <input type="text" name="userid" value="<?php echo e($filters['userid'] ?? ''); ?>" class="pw-input" placeholder="e.g. 1024">
            </div>
            <div>
                <label class="pw-label">Account Name</label>
                <input type="text" name="username" value="<?php echo e($filters['username'] ?? ''); ?>" class="pw-input" placeholder="e.g. pw176">
            </div>
            <div>
                <label class="pw-label">Character ID</label>
                <input type="text" name="role_id" value="<?php echo e($filters['role_id'] ?? ''); ?>" class="pw-input" placeholder="e.g. 1024">
            </div>
            <div>
                <label class="pw-label">Character Name</label>
                <input type="text" name="role_name" value="<?php echo e($filters['role_name'] ?? ''); ?>" class="pw-input" placeholder="cls0gender0">
            </div>

            <div class="md:col-span-4 flex items-center gap-2">
                <button class="pw-btn pw-btn-primary">
                    <?php echo $__env->make('panel._icon', ['name' => 'search', 'size' => 14], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    Search
                </button>
                <?php $anyFilter = ($filters['userid'] ?? '') !== '' || ($filters['username'] ?? '') !== '' || ($filters['role_id'] ?? '') !== '' || ($filters['role_name'] ?? '') !== ''; ?>
                <?php if($anyFilter): ?>
                    <a href="<?php echo e(route('panel.characters.index')); ?>" class="pw-btn">Clear Filters</a>
                <?php endif; ?>
                <span class="ml-auto text-xs text-[var(--color-text-muted)]">
                    Total: <span class="font-semibold text-[var(--color-text)]"><?php echo e(number_format($characters->total())); ?></span> characters
                </span>
            </div>
        </form>
    </div>
</div>


<div class="pw-card">
    <div class="pw-card-title flex items-center justify-between">
        <span class="inline-flex items-center gap-2">
            <span class="text-[var(--color-accent)]">☰</span>
            ALL CHARACTERS
        </span>
        <span class="text-xs text-[var(--color-text-muted)] font-normal normal-case tracking-normal">
            Page <?php echo e($characters->currentPage()); ?> of <?php echo e($characters->lastPage()); ?>

        </span>
    </div>
    <div class="pw-card-body !p-0">
        <div class="overflow-x-auto">
            <table class="pw-table">
                <thead>
                    <tr>
                        <th class="w-10">#</th>
                        <th>Account</th>
                        <th>Character</th>
                        <th class="w-[190px]">Class</th>
                        <th class="w-20 text-center">Level</th>
                        <th class="w-40">Faction</th>
                        <th class="w-24">Status</th>
                        <th class="text-right w-[180px]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $characters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="font-mono text-xs text-[var(--color-text-muted)]">
                            <?php echo e($c->id); ?>

                        </td>

                        <td>
                            <div class="text-sm font-semibold"><?php echo e($c->username ?? '(deleted)'); ?></div>
                            <div class="text-[10px] text-[var(--color-text-muted)] font-mono">uid <?php echo e($c->userid); ?></div>
                        </td>

                        <td>
                            <div class="text-sm font-semibold"><?php echo e($c->name); ?></div>
                            <div class="text-[10px] text-[var(--color-text-muted)] font-mono">role #<?php echo e($c->id); ?></div>
                        </td>

                        <td>
                            <?php echo $__env->make('panel._class-badge', [
                                'race'       => $c->race,
                                'gender'     => $c->gender,
                                'occupation' => $c->cls,
                                'size'       => 'md',
                                'showName'   => true,
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </td>

                        <td class="text-center">
                            <span class="inline-flex items-center justify-center min-w-[2.5rem] px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-sm font-bold tabular-nums">
                                <?php echo e($c->level ?? 0); ?>

                            </span>
                        </td>

                        <td>
                            <?php if(!empty($c->faction_name)): ?>
                                <div class="text-xs font-semibold truncate"><?php echo e($c->faction_name); ?></div>
                                <div class="text-[10px] text-[var(--color-text-muted)]">Lv <?php echo e($c->faction_level ?? 0); ?></div>
                            <?php else: ?>
                                <span class="text-xs text-[var(--color-text-muted)]">—</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php
                                $online = (bool) ($c->online ?? false);
                                $frozen = (bool) ($c->frozen ?? false);
                            ?>
                            <?php if($frozen): ?>
                                <span class="pw-badge pw-badge-warn">Frozen</span>
                            <?php elseif($online): ?>
                                <span class="pw-badge pw-badge-ok">Online</span>
                            <?php else: ?>
                                <span class="pw-badge pw-badge-off">Offline</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-right">
                            <div class="inline-flex items-center gap-1">
                                <a href="<?php echo e(route('panel.characters.show', $c->id)); ?>"
                                   class="pw-btn pw-btn-sm pw-btn-info" title="View details">View</a>
                                <button type="button" disabled class="pw-btn pw-btn-sm" title="Edit XML (coming)">XML</button>
                                <button type="button" disabled class="pw-btn pw-btn-sm pw-btn-warn" title="Rename (coming)">Rename</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="text-center py-10 text-[var(--color-text-muted)]">
                            <div class="text-3xl mb-2">○</div>
                            No characters found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3"><?php echo e($characters->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('panel.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('panel.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/pw-panel/resources/views/panel/character/index.blade.php ENDPATH**/ ?>