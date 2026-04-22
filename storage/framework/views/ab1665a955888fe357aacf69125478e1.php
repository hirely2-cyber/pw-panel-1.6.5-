<?php $__env->startSection('title', 'Character #'.$char->id); ?>
<?php $__env->startSection('breadcrumb', 'Characters / '.$char->name); ?>

<?php
    $lv         = $summary['level']          ?? $char->role_level;
    $lv2        = $summary['level2']         ?? 0;
    $exp        = $summary['exp']            ?? 0;
    $sp         = $summary['sp']            ?? 0;
    $pp         = $summary['pp']            ?? 0;
    $hp         = $summary['hp']            ?? 0;
    $mp         = $summary['mp']            ?? 0;
    $rep        = $summary['reputation']     ?? 0;
    $money      = $summary['money']          ?? 0;
    $storeMoney = $summary['storehouse_money'] ?? 0;
    $lastLogin  = $summary['lastlogin_time'] ?? 0;
    $createTime = $summary['create_time']    ?? 0;
    $deleteTime = $summary['delete_time']    ?? 0;
    $charStatus = $summary['char_status']    ?? 1;
    $posx       = $summary['posx']           ?? 0.0;
    $posy       = $summary['posy']           ?? 0.0;
    $posz       = $summary['posz']           ?? 0.0;
    $worldtag   = $summary['worldtag']       ?? 0;
    $invState   = $summary['invader_state']  ?? 0;
    $invTime    = $summary['invader_time']   ?? 0;
    $pariah     = $summary['pariah_time']    ?? 0;
    $spouse     = $summary['spouse']         ?? 0;
    $hasRoleBin = $summary !== null;

    $cultivations = config('pw_classes.cultivations', []);
    // Find first cultivation entry matching the stored level2 value
    $cultName = collect($cultivations)->first(fn($c) => $c['v'] == $lv2)['n'] ?? 'Unknown';

    $fmtDate = fn ($ts) => $ts > 0 ? date('Y-m-d · H:i:s', (int) $ts) : '—';
    $fmtGold = fn ($m)  => number_format(intdiv((int) $m, 10000)).'.'.str_pad((string)(((int) $m) % 10000), 4, '0', STR_PAD_LEFT);

    $prop = $summary['property'] ?? [];
    $race = config('pw_classes.races.'.($summary['race'] ?? $char->role_race ?? 0).'.name', 'Unknown');
    $cls  = config('pw_classes.occupations.'.($summary['cls'] ?? $char->role_occupation ?? 0).'.name', 'Unknown');
    $gend = (($summary['gender'] ?? $char->role_gender ?? 0) == 0) ? 'Male' : 'Female';
    $maxAp = (int) ($prop['max_ap'] ?? 0);
?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('panel._flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<div class="pw-card mb-3">
    <div class="pw-card-body !py-2 flex flex-wrap items-center gap-3">
        <div class="inline-flex items-center gap-2">
            <span class="text-[var(--color-accent)]">◉</span>
            <span class="text-[13px] font-bold tracking-wide">Character ID: <?php echo e($char->id); ?></span>
        </div>
        <div class="inline-flex items-center gap-1 text-[11px] text-[var(--color-text-muted)]">
            <span>Owner:</span>
            <strong class="text-[var(--color-text)]"><?php echo e($char->username ?? '(deleted)'); ?></strong>
            <span class="font-mono">#<?php echo e($char->userid); ?></span>
        </div>
        <span class="pw-badge <?php echo e($hasRoleBin ? 'pw-badge-ok' : 'pw-badge-danger'); ?> !text-[10px] !py-0 !px-1.5">
            <?php echo e($hasRoleBin ? 'Online DB' : 'Offline'); ?>

        </span>
        <div class="ml-auto flex items-center gap-1.5">
            <span class="text-[10px] text-[var(--color-text-muted)]">v<strong class="text-[var(--color-text)] font-mono"><?php echo e($version); ?></strong></span>
            <a href="<?php echo e(route('panel.characters.xml', $char->id)); ?>" class="pw-btn pw-btn-sm">&lt;/&gt; Raw XML</a>
            <a href="<?php echo e(route('panel.characters.index')); ?>" class="pw-btn pw-btn-sm">← Back</a>
        </div>
    </div>
</div>

<?php
    $secLabel = 'text-[10px] uppercase tracking-wider font-bold text-[var(--color-text-muted)] mb-1';
    $divider  = 'border-t border-dashed border-[var(--color-border)] pt-2 mt-2';
?>

<form method="post" action="<?php echo e(route('panel.characters.update', $char->id)); ?>">
    <?php echo method_field('POST'); ?>
    <?php echo csrf_field(); ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 items-start">

        
        <div class="pw-card">
            <div class="pw-card-title">
                <span class="inline-flex items-center gap-2">
                    <span class="text-[var(--color-accent)]">◉</span>
                    Character Info
                </span>
            </div>
            <div class="pw-card-body !py-2 text-[12px] space-y-3">

                
                <div class="flex items-center gap-3 pb-2 border-b border-dashed border-[var(--color-border)]">
                    <?php echo $__env->make('panel._class-badge', [
                        'occupation' => $char->role_occupation,
                        'gender'     => $char->role_gender,
                        'race'       => $char->role_race,
                        'size'       => 'md',
                        'showName'   => true,
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                
                <div>
                    <div class="<?php echo e($secLabel); ?>">General</div>
                    <dl class="pw-kv">
                        <dt>Status</dt><dd class="font-mono"><?php echo e($charStatus); ?></dd>
                        <dt>Created</dt><dd class="font-mono text-[11px]"><?php echo e($fmtDate($createTime)); ?></dd>
                        <dt>Deleted</dt><dd class="font-mono text-[11px]"><?php echo e($fmtDate($deleteTime)); ?></dd>
                        <dt>Last Login</dt><dd class="font-mono text-[11px]"><?php echo e($fmtDate($lastLogin)); ?></dd>
                    </dl>
                </div>

                
                <div class="<?php echo e($divider); ?>">
                    <div class="<?php echo e($secLabel); ?>">Location</div>
                    <div class="space-y-1.5">
                        <label class="pw-field">
                            <span>World ID</span>
                            <input type="number" name="world_tag" value="<?php echo e($worldtag); ?>" class="pw-input">
                        </label>
                        <label class="pw-field">
                            <span>Position X</span>
                            <input type="number" step="0.01" name="pos_x" value="<?php echo e(round($posx, 2)); ?>" class="pw-input">
                        </label>
                        <label class="pw-field">
                            <span>Position Z</span>
                            <input type="number" step="0.01" name="pos_z" value="<?php echo e(round($posz, 2)); ?>" class="pw-input">
                        </label>
                        <label class="pw-field">
                            <span>Altitude Y</span>
                            <input type="number" step="0.01" name="pos_y" value="<?php echo e(round($posy, 2)); ?>" class="pw-input">
                        </label>
                    </div>
                </div>

                
                <div class="<?php echo e($divider); ?>">
                    <div class="<?php echo e($secLabel); ?>">PK</div>
                    <dl class="pw-kv">
                        <dt>PK Mode</dt><dd><?php echo e($invState ? 'On' : 'Off'); ?></dd>
                        <dt>Invader Time</dt><dd class="font-mono"><?php echo e($invTime); ?></dd>
                        <dt>Pariah Time</dt><dd class="font-mono"><?php echo e($pariah); ?></dd>
                    </dl>
                </div>

                
                <?php if(!empty($cubi)): ?>
                    <div class="<?php echo e($divider); ?>">
                        <div class="<?php echo e($secLabel); ?>">
                            Cubi
                            <?php if(empty($cubi['online'])): ?>
                                <span class="text-[9px] font-normal text-red-400 normal-case ml-1">(gamedbd offline)</span>
                            <?php endif; ?>
                        </div>
                        <?php if(!empty($cubi['online'])): ?>
                            <dl class="pw-kv">
                                <dt>Balance</dt><dd class="font-mono font-semibold"><?php echo e(number_format($cubi['balance'], 2)); ?></dd>
                                <dt>Purchased</dt><dd class="font-mono"><?php echo e(number_format($cubi['purchased'], 2)); ?></dd>
                                <dt>Bought</dt><dd class="font-mono"><?php echo e(number_format($cubi['bought'], 2)); ?></dd>
                                <dt>Used</dt><dd class="font-mono"><?php echo e(number_format($cubi['used'], 2)); ?></dd>
                                <dt>Sold</dt><dd class="font-mono"><?php echo e(number_format($cubi['sold'], 2)); ?></dd>
                            </dl>
                        <?php else: ?>
                            <div class="text-[11px] text-[var(--color-text-muted)]">Live balance tidak tersedia.</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        
        <div class="pw-card">
            <div class="pw-card-title">
                <span class="inline-flex items-center gap-2">
                    <span class="text-[var(--color-accent)]">✎</span>
                    Character Properties — Admin
                </span>
            </div>
            <div class="pw-card-body !py-2 text-[12px] space-y-3">

                
                <dl class="pw-kv">
                    <dt>Level</dt><dd class="font-mono font-semibold"><?php echo e($lv); ?></dd>
                    <dt>Race</dt><dd><?php echo e($race); ?></dd>
                    <dt>Class</dt><dd><?php echo e($cls); ?></dd>
                    <dt>Gender</dt><dd><?php echo e($gend); ?></dd>
                    <dt>HP (Max)</dt><dd class="font-mono"><?php echo e(number_format($hp)); ?></dd>
                    <dt>MP (Max)</dt><dd class="font-mono"><?php echo e(number_format($mp)); ?></dd>
                    <dt>Spouse</dt><dd class="font-mono"><?php echo e($spouse ?: '—'); ?></dd>
                </dl>

                
                <div class="<?php echo e($divider); ?>">
                    <div class="<?php echo e($secLabel); ?>">Editable Stats</div>
                    <div class="grid grid-cols-2 gap-2 mb-1.5">
                        <label class="pw-field">
                            <span>Reputation</span>
                            <input type="number" name="reputation" value="<?php echo e($rep); ?>" class="pw-input">
                        </label>
                        <label class="pw-field">
                            <span>EXP</span>
                            <input type="number" name="exp" value="<?php echo e($exp); ?>" class="pw-input">
                        </label>
                    </div>
                    <div class="space-y-1.5">
                        <label class="pw-field">
                            <span>SP (Spirit)</span>
                            <input type="number" name="sp" value="<?php echo e($sp); ?>" class="pw-input">
                        </label>
                        <label class="pw-field">
                            <span>
                                Cultivation
                                <span class="text-[var(--color-text-muted)] font-normal normal-case ml-1">(raw: <span class="font-mono text-[var(--color-accent)]"><?php echo e($lv2); ?></span>)</span>
                            </span>
                            <select name="level2" class="pw-input">
                                <?php if(collect($cultivations)->every(fn($c) => $c['v'] != $lv2)): ?>
                                    <option value="<?php echo e($lv2); ?>" selected><?php echo e($lv2); ?> — (unknown / current)</option>
                                <?php endif; ?>
                                <?php $__currentLoopData = $cultivations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cult): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($cult['v']); ?>" <?php if($cult['v'] == $lv2): echo 'selected'; endif; ?>><?php echo e($cult['v']); ?> — <?php echo e($cult['n']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </label>
                        <label class="pw-field">
                            <span>Vigor Points (Chi)</span>
                            <select name="max_ap" class="pw-input">
                                <?php $__currentLoopData = config('pw_classes.vigor_points', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($id); ?>" <?php if($id == $maxAp): echo 'selected'; endif; ?>><?php echo e($name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </label>
                    </div>
                </div>

                <?php if(!empty($prop)): ?>
                    
                    <div class="<?php echo e($divider); ?>">
                        <div class="<?php echo e($secLabel); ?>">Attributes</div>
                        <dl class="pw-kv">
                            <dt>Free Points</dt><dd class="font-mono col-span-3"><?php echo e($pp); ?></dd>
                            <dt>CON</dt><dd class="font-mono"><?php echo e((int) ($prop['vitality'] ?? 0)); ?></dd>
                            <dt>INT</dt><dd class="font-mono"><?php echo e((int) ($prop['energy'] ?? 0)); ?></dd>
                            <dt>STR</dt><dd class="font-mono"><?php echo e((int) ($prop['strength'] ?? 0)); ?></dd>
                            <dt>AGI</dt><dd class="font-mono"><?php echo e((int) ($prop['agility'] ?? 0)); ?></dd>
                        </dl>
                    </div>

                    
                    <div class="<?php echo e($divider); ?>">
                        <div class="<?php echo e($secLabel); ?>">Base Stats</div>
                        <dl class="pw-kv">
                            <dt>P-Def</dt><dd class="font-mono"><?php echo e((int) ($prop['defense'] ?? 0)); ?></dd>
                            <dt>P-Atk</dt><dd class="font-mono"><?php echo e((int) ($prop['damage_low'] ?? 0)); ?> – <?php echo e((int) ($prop['damage_high'] ?? 0)); ?></dd>
                            <dt>M-Def</dt><dd class="font-mono">0</dd>
                            <dt>M-Atk</dt><dd class="font-mono"><?php echo e((int) ($prop['damage_magic_low'] ?? 0)); ?> – <?php echo e((int) ($prop['damage_magic_high'] ?? 0)); ?></dd>
                        </dl>
                    </div>
                <?php endif; ?>

                
                <div class="<?php echo e($divider); ?>">
                    <button type="submit" class="pw-btn pw-btn-primary w-full">
                        💾 Save Character Data
                    </button>
                </div>

            </div>
        </div>

        
        <div class="pw-card">
            <div class="pw-card-title">
                <span class="inline-flex items-center gap-2">
                    <span class="text-[var(--color-accent)]">◎</span>
                    Items &amp; Coins
                </span>
            </div>
            <div class="pw-card-body !py-2 text-[12px] space-y-3">
                <?php if($summary): ?>
                    
                    <?php echo $__env->make('panel.character._equipment-paperdoll', [
                        'equipment' => $summary['equipment'],
                        'gender'    => $summary['gender'] ?? $char->role_gender,
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    
                    <?php echo $__env->make('panel.character._inv-grid', [
                        'title'     => 'Inventory',
                        'container' => $summary['pocket'],
                        'cols'      => 8,
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    
                    <?php echo $__env->make('panel.character._inv-grid', [
                        'title'     => 'Storehouse',
                        'container' => $summary['storehouse'],
                        'cols'      => 8,
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    
                    <details>
                        <summary class="text-[11px] text-[var(--color-text-muted)] cursor-pointer select-none py-1">
                            Wardrobe / Material / Cards ›
                        </summary>
                        <div class="space-y-3 mt-2">
                            <?php echo $__env->make('panel.character._inv-grid', ['title' => 'Wardrobe',         'container' => $summary['wardrobe'],  'cols' => 8], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php echo $__env->make('panel.character._inv-grid', ['title' => 'Material Storage', 'container' => $summary['material'], 'cols' => 8], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php echo $__env->make('panel.character._inv-grid', ['title' => 'Magic Cards',      'container' => $summary['card'],     'cols' => 12], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    </details>

                    
                    <div class="<?php echo e($divider); ?>">
                        <div class="<?php echo e($secLabel); ?>">Selected Item</div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <label class="pw-field col-span-2">
                                <span>Item Name</span>
                                <input id="si-name" class="pw-input" readonly placeholder="—">
                            </label>
                            <label class="pw-field">
                                <span>Item ID</span>
                                <input id="si-id" class="pw-input font-mono" readonly placeholder="—">
                            </label>
                            <label class="pw-field">
                                <span>Group</span>
                                <input id="si-group" class="pw-input font-mono" readonly placeholder="—">
                            </label>
                            <label class="pw-field">
                                <span>Index</span>
                                <input id="si-index" class="pw-input font-mono" readonly placeholder="—">
                            </label>
                            <label class="pw-field">
                                <span>Position</span>
                                <input id="si-pos" class="pw-input font-mono" readonly placeholder="—">
                            </label>
                            <label class="pw-field">
                                <span>Guid 1</span>
                                <input id="si-guid1" class="pw-input font-mono" readonly placeholder="—">
                            </label>
                            <label class="pw-field">
                                <span>Guid 2</span>
                                <input id="si-guid2" class="pw-input font-mono" readonly placeholder="—">
                            </label>
                            <label class="pw-field">
                                <span>Proctype</span>
                                <input id="si-proctype" class="pw-input font-mono" readonly placeholder="—">
                            </label>
                            <label class="pw-field">
                                <span>Mask</span>
                                <input id="si-mask" class="pw-input font-mono" readonly placeholder="—">
                            </label>
                            <label class="pw-field">
                                <span>Stacked</span>
                                <input id="si-count" class="pw-input font-mono" readonly placeholder="—">
                            </label>
                            <label class="pw-field">
                                <span>Max Stack</span>
                                <input id="si-maxcount" class="pw-input font-mono" readonly placeholder="—">
                            </label>
                            <label class="pw-field">
                                <span>Expire</span>
                                <input id="si-expire" class="pw-input font-mono" readonly placeholder="—">
                            </label>
                            <label class="pw-field col-span-2">
                                <span>Hex Data</span>
                                <input id="si-hex" class="pw-input font-mono text-[10px]" readonly placeholder="—">
                            </label>
                        </div>
                    </div>

                    
                    <div class="<?php echo e($divider); ?>">
                        <div class="<?php echo e($secLabel); ?>">Coins</div>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="pw-field">
                                <span>Pocket</span>
                                <input type="number" name="pocket_money" value="<?php echo e($money); ?>" class="pw-input">
                            </label>
                            <label class="pw-field">
                                <span>Storehouse</span>
                                <input type="number" name="store_money" value="<?php echo e($storeMoney); ?>" class="pw-input">
                            </label>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="text-[11px] text-[var(--color-text-muted)] py-4 text-center">
                        Could not load character data (socket offline).
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</form>


<details class="pw-card mt-3">
    <summary class="pw-card-title cursor-pointer select-none">
        <span class="inline-flex items-center gap-2">
            <span class="text-[var(--color-accent)]">›</span>
            RAW DATA (SQL row)
        </span>
    </summary>
    <div class="pw-card-body">
        <pre class="p-3 bg-[var(--color-surface-2)] border border-[var(--color-border)] rounded overflow-x-auto text-[11px] text-[var(--color-text-soft)]"><?php echo e(json_encode($char, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
    </div>
</details>

<script>
function pwSelectItem(el) {
    const d = JSON.parse(el.dataset.item || '{}');
    document.getElementById('si-name').value     = d.name     ?? '';
    document.getElementById('si-id').value       = d.id       ?? '';
    document.getElementById('si-group').value    = d.group    ?? '';
    document.getElementById('si-index').value    = d.index    ?? '';
    document.getElementById('si-guid1').value    = d.guid1    ?? '';
    document.getElementById('si-guid2').value    = d.guid2    ?? '';
    document.getElementById('si-proctype').value = d.proctype ?? '';
    document.getElementById('si-mask').value     = d.mask     ?? '';
    document.getElementById('si-pos').value      = d.pos      ?? '';
    document.getElementById('si-expire').value   = d.expire   ?? '';
    document.getElementById('si-count').value    = d.count    ?? '';
    document.getElementById('si-maxcount').value = d.max_count ?? '';
    document.getElementById('si-hex').value      = d.data     ?? '';
    document.querySelectorAll('.pw-item-cell.has-item')
        .forEach(c => c.classList.remove('ring', 'ring-[var(--color-accent)]'));
    el.classList.add('ring', 'ring-[var(--color-accent)]');
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('panel.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/pw-panel/resources/views/panel/character/show.blade.php ENDPATH**/ ?>