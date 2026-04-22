
<?php
    $fmt = function (int $b) {
        if ($b <= 0) return '0 B';
        $u = ['B','KB','MB','GB','TB'];
        $i = min(count($u)-1, (int) floor(log($b, 1024)));
        return number_format($b / (1024 ** $i), $i >= 2 ? 1 : 0).' '.$u[$i];
    };
    $kindBadge = [
        'sql'     => ['pw-badge-info',  'SQL'],
        'server'  => ['pw-badge-warn',  'Server'],
        'archive' => ['pw-badge-muted', 'Archive'],
    ];
?>
<div class="pw-card">
    <div class="pw-card-title flex items-center justify-between gap-2">
        <span>Backup Files</span>
        <span class="text-xs font-normal text-[var(--color-text-soft)]">
            <?php echo e($backupList->count()); ?> file(s) ·
            <code><?php echo e($backupDir ?: '—'); ?></code>
        </span>
    </div>
    <div class="pw-card-body !p-0">
        <table class="pw-table">
            <thead>
                <tr>
                    <th>Filename</th>
                    <th>Kind</th>
                    <th class="text-right">Size</th>
                    <th>Created</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $backupList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php [$bc,$bl] = $kindBadge[$b['kind']] ?? ['pw-badge-muted','File']; ?>
                    <tr>
                        <td>
                            <code class="text-[var(--color-text)]"><?php echo e($b['name']); ?></code>
                        </td>
                        <td><span class="pw-badge <?php echo e($bc); ?>"><?php echo e($bl); ?></span></td>
                        <td class="text-right font-mono text-xs"><?php echo e($fmt($b['size'])); ?></td>
                        <td class="text-xs text-[var(--color-text-soft)]">
                            <?php echo e(\Carbon\Carbon::createFromTimestamp($b['mtime'])->format('Y-m-d H:i')); ?>

                        </td>
                        <td class="text-right">
                            <div class="inline-flex gap-1">
                                <a href="<?php echo e(route('panel.server.backup.download', ['name' => $b['name']])); ?>"
                                   class="pw-btn pw-btn-sm pw-btn-primary">
                                    ⇣ Download
                                </a>
                                <button type="button"
                                        class="pw-btn pw-btn-sm pw-btn-danger"
                                        data-del="<?php echo e($b['name']); ?>"
                                        onclick="pwAskDelete(this)">
                                    ✕ Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="text-center text-sm text-[var(--color-text-soft)] py-6">
                            No backup files yet.
                            <?php if(! is_dir($backupDir)): ?>
                                Directory <code><?php echo e($backupDir); ?></code> does not exist.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function pwAskDelete(btn) {
    const name = btn.dataset.del;
    pwConfirm({
        title: 'Delete Backup',
        message: 'Delete ' + name + '?\nThis cannot be undone.',
        confirmText: 'Delete',
        confirmStyle: 'danger',
        onConfirm: () => pwDeleteBackup(name)
    });
}
function pwDeleteBackup(name) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?php echo e(route("panel.server.backup.delete", ["name" => "__NAME__"])); ?>'.replace('__NAME__', encodeURIComponent(name));
    const add = (n, v) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = n; i.value = v; form.appendChild(i); };
    add('_token', token);
    add('_method', 'DELETE');
    document.body.appendChild(form);
    form.submit();
}
</script>
<?php /**PATH /var/www/html/pw-panel/resources/views/panel/_backup-list.blade.php ENDPATH**/ ?>