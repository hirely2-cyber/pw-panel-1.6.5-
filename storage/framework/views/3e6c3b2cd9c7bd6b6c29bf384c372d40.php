
<?php
    $bySlot = [];
    foreach (($equipment['items'] ?? []) as $it) {
        $bySlot[(int) $it['pos']] = $it;
    }
    $used = (int) ($equipment['used'] ?? 0);
    $genderClass = ((int) $gender) === 1 ? 'female' : 'male';
?>
<div class="pw-inv">
    <div class="pw-inv-header">
        <span class="pw-inv-title">Equipment</span>
        <span class="pw-inv-meta">equipped: <?php echo e($used); ?></span>
    </div>
    <div class="player__equipment <?php echo e($genderClass); ?>">
        <?php $__currentLoopData = range(0, 31); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $slotItem = $bySlot[$slot] ?? null;
                $slotCat  = $slotItem ? \App\Services\PW\Items\ItemCatalog::item((int) $slotItem['id']) : null;
                $slotName = $slotCat['name'] ?? ($slotItem ? '#'.$slotItem['id'] : '');
                $slotIc   = $slotItem ? \App\Services\PW\Items\ItemCatalog::iconDataUrl((int) $slotItem['id']) : '';
            ?>
            <div class="player__equipment-item cell-<?php echo e($slot); ?> pw-item-cell <?php echo e($slotItem ? 'has-item' : 'empty'); ?>"
                 title="Slot <?php echo e($slot); ?><?php echo e($slotItem ? ' — '.$slotName : ''); ?>"
                 <?php if($slotItem): ?>
                     data-item="<?php echo e(json_encode([
                         'name'     => $slotName,
                         'id'       => $slotItem['id'],
                         'group'    => $slotCat['list'] ?? 0,
                         'index'    => basename(str_replace('\\\\', '/', $slotCat['icon'] ?? '')),
                         'guid1'    => $slotItem['guid1'],
                         'guid2'    => $slotItem['guid2'],
                         'proctype' => $slotItem['proctype'],
                         'mask'     => $slotItem['mask'],
                         'pos'      => $slotItem['pos'],
                         'expire'   => $slotItem['expire_date'],
                         'count'    => $slotItem['count'],
                         'max_count'=> $slotItem['max_count'],
                         'data'     => $slotItem['data'] ?? '',
                     ])); ?>"
                     onclick="pwSelectItem(this)"
                 <?php endif; ?>>
                <?php if($slotItem && $slotIc): ?>
                    <img src="<?php echo e($slotIc); ?>" alt="<?php echo e(e($slotName)); ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php /**PATH /var/www/html/pw-panel/resources/views/panel/character/_equipment-paperdoll.blade.php ENDPATH**/ ?>