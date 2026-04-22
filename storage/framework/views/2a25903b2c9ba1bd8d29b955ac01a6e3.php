
<?php
    $cols = $cols ?? 8;
    $cap  = (int) ($container['capacity'] ?? 0);
    $used = (int) ($container['used'] ?? 0);
    $bySlot = [];
    foreach (($container['items'] ?? []) as $it) {
        $bySlot[(int) $it['pos']] = $it;
    }
?>
<div class="pw-inv">
    <div class="pw-inv-header">
        <span class="pw-inv-title"><?php echo e($title); ?></span>
        <span class="pw-inv-meta">cells: <?php echo e($cap); ?> · used: <?php echo e($used); ?></span>
    </div>
    <?php if($cap > 0): ?>
        <div class="pw-inv-grid" style="grid-template-columns: repeat(<?php echo e($cols); ?>, 34px)">
            <?php for($i = 0; $i < $cap; $i++): ?>
                <?php echo $__env->make('panel.character._item-cell', ['item' => $bySlot[$i] ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endfor; ?>
        </div>
    <?php else: ?>
        <div class="pw-inv-empty">— not allocated —</div>
    <?php endif; ?>
</div>
<?php /**PATH /var/www/html/pw-panel/resources/views/panel/character/_inv-grid.blade.php ENDPATH**/ ?>