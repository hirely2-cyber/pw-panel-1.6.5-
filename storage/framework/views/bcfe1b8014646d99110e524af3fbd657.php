
<?php
    $size = $size ?? 32;
    $cat  = $item ? \App\Services\PW\Items\ItemCatalog::item((int) $item['id']) : null;
    $name = $cat['name'] ?? ('#'.($item['id'] ?? 0));
    $icon = $item ? \App\Services\PW\Items\ItemCatalog::iconDataUrl((int) $item['id']) : '';
    $count = (int) ($item['count'] ?? 0);
?>
<div class="pw-item-cell <?php echo e($item ? 'has-item' : 'empty'); ?>"
     style="width:<?php echo e($size); ?>px;height:<?php echo e($size); ?>px"
     <?php if($item): ?>
         title="<?php echo e($name); ?> (#<?php echo e($item['id']); ?>) ×<?php echo e($count); ?>"
         data-item="<?php echo e(json_encode([
             'name'     => $name,
             'id'       => $item['id'],
             'group'    => $cat['list'] ?? 0,
             'index'    => basename(str_replace('\\', '/', $cat['icon'] ?? '')),
             'guid1'    => $item['guid1'],
             'guid2'    => $item['guid2'],
             'proctype' => $item['proctype'],
             'mask'     => $item['mask'],
             'pos'      => $item['pos'],
             'expire'   => $item['expire_date'],
             'count'    => $item['count'],
             'max_count'=> $item['max_count'],
             'data'     => $item['data'] ?? '',
         ])); ?>"
         onclick="pwSelectItem(this)"
     <?php endif; ?>>
    <?php if($item && $icon): ?>
        <img src="<?php echo e($icon); ?>" alt="<?php echo e(e($name)); ?>" loading="lazy">
        <?php if($count > 1): ?>
            <span class="pw-item-count"><?php echo e($count > 9999 ? number_format($count) : $count); ?></span>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php /**PATH /var/www/html/pw-panel/resources/views/panel/character/_item-cell.blade.php ENDPATH**/ ?>