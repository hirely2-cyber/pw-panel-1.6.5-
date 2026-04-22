<?php $__env->startSection('title', 'Server Control'); ?>
<?php $__env->startSection('breadcrumb', 'Server Control'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('panel._flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('panel._server-info', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php
    $anyOnline = collect($status)->contains(fn($s) => $s['online'] ?? false);
    // Precompute maps for the Alpine component (avoid @json on multi-line closures)
    $pwServicesCfg = config('pw.services', []);
    $pwSvcLabels = [];
    $pwInstMap   = [];
    foreach ($pwServicesCfg as $k => $s) {
        $pwSvcLabels[$k] = $s['label'] ?? ucwords(str_replace(['_','-'],' ',$k));
        $pwInstMap[$k]   = (int) ($s['instances'] ?? 1);
    }
    $pwStopOrder  = array_values(config('pw.stop_order', []));
    $pwStartOrder = array_keys(config('pw.start_order', []));
?>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

    
    <div class="pw-card" x-data="pwServicesPanel()" x-init="init()">
        <div class="pw-card-title flex items-center justify-between">
            <span class="inline-flex items-center gap-2">
                <span class="text-[var(--color-accent)]">◉</span>
                SERVICES
                
                <span x-show="busy" x-cloak
                      x-transition.opacity
                      class="ml-2 inline-flex items-center gap-1.5 text-[11px] font-normal normal-case tracking-normal">
                    <span class="inline-block w-3 h-3 border-2 rounded-full animate-spin"
                          :class="action === 'stop'
                              ? 'border-rose-200 border-t-rose-500'
                              : 'border-emerald-200 border-t-emerald-500'"></span>
                    <span :class="action === 'stop' ? 'text-rose-600' : 'text-emerald-600'">
                        <span x-text="action === 'stop' ? 'Stopping' : 'Starting'"></span>
                        <span x-text="progressDone + '/' + progressTotal"></span>
                        <span x-show="currentLabel" x-cloak>·
                            <span class="font-semibold" x-text="currentLabel"></span>
                        </span>
                    </span>
                </span>
            </span>
            <span class="text-xs font-semibold <?php echo e($anyOnline ? 'text-emerald-600' : 'text-rose-500'); ?>"
                  x-show="!busy">
                <?php echo e($anyOnline ? 'ONLINE' : 'OFFLINE'); ?>

            </span>
        </div>

        
        <div class="h-0.5 bg-slate-100 overflow-hidden" x-show="busy" x-cloak>
            <div class="h-full transition-all duration-300 ease-out"
                 :class="action === 'stop' ? 'bg-rose-500' : 'bg-emerald-500'"
                 :style="'width:' + (progressTotal ? Math.round(progressDone/progressTotal*100) : 0) + '%'"></div>
        </div>

        <div class="pw-card-body">

            
            <div class="text-[11px] font-semibold tracking-wide text-[var(--color-text-soft)] uppercase mb-2">Memory Usage</div>
            <div class="grid grid-cols-4 gap-2 mb-2">
                <?php
                    $cells = [
                        ['Total',      $memory['total'],      'text-slate-700'],
                        ['Apps',       $memory['apps'],       'text-sky-600'],
                        ['Buff/Cache', $memory['buff_cache'], 'text-amber-500'],
                        ['Available',  $memory['available'],  'text-emerald-600'],
                    ];
                ?>
                <?php $__currentLoopData = $cells; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$lbl,$val,$cls]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded border border-[var(--color-border-2)] bg-[var(--color-surface-2,#f7f9fb)] px-3 py-2 text-center">
                        <div class="text-[11px] text-[var(--color-text-soft)]"><?php echo e($lbl); ?></div>
                        <div class="font-semibold <?php echo e($cls); ?>"><?php echo e(number_format($val)); ?> MB</div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div class="flex items-center justify-between text-[11px] mb-1">
                <span class="text-sky-600 font-semibold">Apps</span>
                <span class="text-[var(--color-text-soft)]"><?php echo e($memory['apps_pct']); ?>%</span>
            </div>
            <div class="h-1.5 bg-slate-100 rounded overflow-hidden mb-2">
                <div class="h-full bg-sky-500" style="width: <?php echo e($memory['apps_pct']); ?>%"></div>
            </div>

            
            <div class="flex items-center justify-between text-[11px] mb-1">
                <span class="text-amber-500 font-semibold">Buff/Cache</span>
                <span class="text-[var(--color-text-soft)]"><?php echo e($memory['buff_cache_pct']); ?>%</span>
            </div>
            <div class="h-1.5 bg-slate-100 rounded overflow-hidden mb-3">
                <div class="h-full bg-amber-400" style="width: <?php echo e($memory['buff_cache_pct']); ?>%"></div>
            </div>

            
            <div class="flex items-center justify-between text-[11px] mb-1">
                <span class="font-semibold inline-flex items-center gap-1.5">
                    <span class="text-[var(--color-text-soft)]">Swap</span>
                    <?php if($memory['swap_total'] <= 0): ?>
                        <span class="pw-badge pw-badge-warn !text-[9px] !px-1.5 !py-0"
                              title="Swap is not configured on this host. Running out of RAM may crash services.">
                            Inactive
                        </span>
                    <?php endif; ?>
                </span>
                <span class="text-[var(--color-text-soft)]">
                    <?php if($memory['swap_total'] > 0): ?>
                        <?php echo e($memory['swap_pct']); ?>% · <?php echo e(number_format($memory['swap_used'])); ?> / <?php echo e(number_format($memory['swap_total'])); ?> MB
                    <?php else: ?>
                        Not configured
                    <?php endif; ?>
                </span>
            </div>
            <div class="h-1.5 bg-slate-100 rounded overflow-hidden mb-4">
                <div class="h-full bg-rose-400" style="width: <?php echo e($memory['swap_pct']); ?>%"></div>
            </div>

            
            <div class="divide-y divide-[var(--color-border-2)] border border-[var(--color-border-2)] rounded">
            <?php $__currentLoopData = config('pw.services'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $svc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $st = $status[$key] ?? ['status'=>'unknown','online'=>false,'count'=>0];
                    $program = $key === 'auth' ? $server->auth_type : ($svc['program'] ?? $key);
                    $label   = $svc['label'] ?? ucwords(str_replace(['_','-'],' ',$key));
                    $inst    = (int) ($svc['instances'] ?? 1);
                ?>
                <div class="flex items-center justify-between px-3 py-2 text-sm transition-colors duration-300"
                     :class="{
                         'bg-amber-50': svcState['<?php echo e($key); ?>'] && svcState['<?php echo e($key); ?>'].phase === 'running',
                         'bg-emerald-50/60': svcState['<?php echo e($key); ?>'] && svcState['<?php echo e($key); ?>'].phase === 'ok' && svcState['<?php echo e($key); ?>'].label === 'Online',
                         'bg-rose-50/60': svcState['<?php echo e($key); ?>'] && (svcState['<?php echo e($key); ?>'].phase === 'fail' || (svcState['<?php echo e($key); ?>'].phase === 'ok' && svcState['<?php echo e($key); ?>'].label === 'Offline')),
                     }">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="font-semibold"><?php echo e($label); ?></span>
                        <code class="text-[11px] text-[var(--color-text-soft)]">./<?php echo e($program); ?><?php echo e($inst > 1 ? " ×{$inst}" : ''); ?></code>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        
                        <span class="text-[11px] text-[var(--color-text-soft)] tabular-nums"
                              x-show="!(svcState && svcState['<?php echo e($key); ?>'])">
                            <?php if(($st['rss_mb'] ?? 0) > 0): ?>
                                <span class="text-sky-600 font-semibold"><?php echo e(number_format($st['rss_mb'])); ?></span>
                            <?php else: ?>
                                <span class="text-[var(--color-text-muted)]">0</span>
                            <?php endif; ?>
                            <span class="text-[var(--color-text-muted)]">MB</span>
                        </span>

                        
                        <span class="text-xs text-[var(--color-text-soft)]"
                              x-show="!(svcState && svcState['<?php echo e($key); ?>'])">
                            <?php echo e($st['count']); ?>

                        </span>

                        
                        <template x-if="svcState && svcState['<?php echo e($key); ?>']">
                            <span class="pw-badge inline-flex items-center gap-1 transition-all duration-200"
                                  :class="{
                                      'pw-badge-warn': svcState['<?php echo e($key); ?>'].phase === 'running',
                                      'pw-badge-ok':   svcState['<?php echo e($key); ?>'].phase === 'ok' && svcState['<?php echo e($key); ?>'].label === 'Online',
                                      'pw-badge-off':  svcState['<?php echo e($key); ?>'].phase === 'fail' || (svcState['<?php echo e($key); ?>'].phase === 'ok' && svcState['<?php echo e($key); ?>'].label === 'Offline'),
                                  }">
                                
                                <template x-if="svcState['<?php echo e($key); ?>'].phase === 'running'">
                                    <span class="inline-block w-2.5 h-2.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                                </template>
                                
                                <template x-if="svcState['<?php echo e($key); ?>'].phase === 'ok' && svcState['<?php echo e($key); ?>'].label === 'Online'">
                                    <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-8 8a1 1 0 01-1.4 0l-4-4a1 1 0 111.4-1.4L8 12.6l7.3-7.3a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                                </template>
                                
                                <template x-if="svcState['<?php echo e($key); ?>'].phase === 'ok' && svcState['<?php echo e($key); ?>'].label === 'Offline'">
                                    <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 10a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
                                </template>
                                
                                <template x-if="svcState['<?php echo e($key); ?>'].phase === 'fail'">
                                    <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.3 4.3a1 1 0 011.4 0L10 8.6l4.3-4.3a1 1 0 111.4 1.4L11.4 10l4.3 4.3a1 1 0 01-1.4 1.4L10 11.4l-4.3 4.3a1 1 0 01-1.4-1.4L8.6 10 4.3 5.7a1 1 0 010-1.4z" clip-rule="evenodd"/></svg>
                                </template>
                                <span x-text="svcState['<?php echo e($key); ?>'].label"></span>
                            </span>
                        </template>

                        
                        <template x-if="!(svcState && svcState['<?php echo e($key); ?>'])">
                            <?php if($st['online']): ?>
                                <span class="pw-badge pw-badge-ok">Online</span>
                            <?php else: ?>
                                <span class="pw-badge pw-badge-off">Offline</span>
                            <?php endif; ?>
                        </template>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div class="grid grid-cols-2 gap-2 mt-4">
                <button type="button" class="pw-btn pw-btn-success"
                    :disabled="busy"
                    :class="busy ? 'opacity-60 cursor-not-allowed' : ''"
                    @click="pwConfirm({
                        title:'Start Server',
                        message:'Start all services?',
                        confirmText:'Start',
                        confirmStyle:'success',
                        onConfirm: () => runAction('start')
                    })">▶ Start Server</button>

                <button type="button" class="pw-btn pw-btn-danger"
                    :disabled="busy"
                    :class="busy ? 'opacity-60 cursor-not-allowed' : ''"
                    @click="pwConfirm({
                        title:'Stop Server',
                        message:'Stop all services immediately?',
                        confirmText:'Stop',
                        confirmStyle:'danger',
                        onConfirm: () => runAction('stop')
                    })">■ Stop Server</button>

                <button type="button" class="pw-btn pw-btn-warn"
                        onclick="pwConfirm({ title:'Clear RAM Cache', message:'Drop page/dentry/inode caches on the server host?', confirmText:'Clear', confirmStyle:'warn', onConfirm: () => pwPostServer({ service:'all', action:'clear_cache' }) })">✦ Clear RAM</button>
                <button type="button" class="pw-btn"
                        style="background:#7b4dff;color:#fff;border-color:#7b4dff;"
                        onclick="pwConfirm({ title:'Backup Database & Server', message:'Create a new SQL dump AND archive server files now?\n(server archive excludes logs)', confirmText:'Backup', confirmStyle:'primary', onConfirm: () => pwPostServer({ service:'all', action:'backup' }) })">⇣ Backup Now</button>
            </div>

            <div class="mt-3 text-[11px] text-[var(--color-text-soft)] bg-[var(--color-surface-2,#f7f9fb)] border border-[var(--color-border-2)] rounded px-2 py-1.5">
                ↓ Backup stored at:
                <code class="text-[var(--color-accent)]"><?php echo e(rtrim($server->server_path, '/')); ?>/pw_backup_YYYYMMDD.tar.gz</code>
            </div>
        </div>
    </div>

    
    <div x-data="pwMapsPanel()" class="pw-card">
        <div class="pw-card-title flex items-center gap-2 [&::after]:!ml-2">
            <span class="inline-flex items-center gap-2">
                <span class="text-[var(--color-accent)]">▥</span>
                MAPS CONTROL
            </span>
            <div class="flex items-center gap-2 text-xs font-normal ml-auto">
                <label class="inline-flex items-center gap-1" x-show="!countdown">
                    <span>Delay:</span>
                    <input type="number" min="0" x-model.number="delay"
                           class="pw-input !py-1 !px-2 !text-xs" style="width:70px">
                    <span class="text-[var(--color-text-soft)]">sec</span>
                </label>
                <button type="button"
                        x-show="!countdown"
                        @click="startDelayStop()"
                        class="pw-btn pw-btn-sm pw-btn-danger">◷ Delay Stop</button>
                <button type="button"
                        x-show="countdown"
                        x-cloak
                        @click="cancelDelayStop()"
                        class="pw-btn pw-btn-sm pw-btn-warn inline-flex items-center gap-1.5">
                    <span class="inline-block w-2.5 h-2.5 border-2 border-amber-200 border-t-amber-600 rounded-full animate-spin"></span>
                    Cancel (<span x-text="fmtCountdown()" class="tabular-nums font-semibold"></span>)
                </button>
            </div>
        </div>

        
        <div x-show="countdown" x-cloak
             class="mx-3 mt-3 mb-0 rounded border border-rose-200 bg-rose-50 text-rose-700 px-3 py-2 flex items-center justify-between text-xs">
            <span class="inline-flex items-center gap-2">
                <span class="inline-block w-3 h-3 border-2 border-rose-200 border-t-rose-500 rounded-full animate-spin"></span>
                <span>All maps will stop in <strong x-text="fmtCountdown()" class="tabular-nums"></strong>…</span>
            </span>
            <button type="button" class="underline hover:no-underline" @click="cancelDelayStop()">Cancel</button>
        </div>

        <div class="pw-card-body">
            <div class="mb-2">
                <input type="text" x-model="q" placeholder="Search map id / name…"
                       class="pw-input !py-1.5 !text-xs">
            </div>

            <div class="grid grid-cols-2 gap-2">
                
                <div class="border border-[var(--color-border-2)] rounded overflow-hidden flex flex-col" style="min-height:460px;">
                    <div class="px-2.5 py-1.5 bg-[var(--color-surface-2,#f7f9fb)] border-b border-[var(--color-border-2)] flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-emerald-600 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            ONLINE MAPS ( <?php echo e(count($onlineMaps)); ?> )
                        </span>
                        <label class="text-[11px] inline-flex items-center gap-1">
                            <input type="checkbox" class="pw-checkbox" @change="toggleAll('online', $event)">
                        </label>
                    </div>

                    <ul class="flex-1 overflow-y-auto divide-y divide-[var(--color-border-2)] text-sm" data-list="online" style="max-height:520px;">
                        <?php $__empty_1 = true; $__currentLoopData = $onlineMaps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <li class="px-2 py-1 flex items-center gap-2 bg-emerald-50/40"
                                x-show="isVisible(<?php echo \Illuminate\Support\Js::from($m['id'])->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($m['name'])->toHtml() ?>)">
                                <input type="checkbox" class="pw-checkbox"
                                       value="<?php echo e($m['id']); ?>" x-model="onlineSel">
                                <code class="text-[var(--color-accent)] font-semibold text-xs"><?php echo e($m['id']); ?></code>
                                <span class="text-xs truncate flex-1"><?php echo e($m['name']); ?></span>
                                <?php if(!empty($m['cpu'])): ?>
                                    <span class="text-[10px] text-[var(--color-text-soft)] whitespace-nowrap">
                                        <?php echo e($m['cpu']); ?>% / <?php echo e($m['mem']); ?>%
                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <li class="px-3 py-6 text-center text-xs text-[var(--color-text-soft)]">
                                No running maps.
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                
                <div class="border border-[var(--color-border-2)] rounded overflow-hidden flex flex-col" style="min-height:460px;">
                    <div class="px-2.5 py-1.5 bg-[var(--color-surface-2,#f7f9fb)] border-b border-[var(--color-border-2)] flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-[var(--color-text-soft)] flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                            AVAILABLE MAPS ( <?php echo e(count($availableMaps)); ?> )
                        </span>
                        <label class="text-[11px] inline-flex items-center gap-1">
                            <input type="checkbox" class="pw-checkbox" @change="toggleAll('avail', $event)">
                        </label>
                    </div>

                    <ul class="flex-1 overflow-y-auto divide-y divide-[var(--color-border-2)] text-sm" data-list="avail" style="max-height:520px;">
                        <?php $__empty_1 = true; $__currentLoopData = $availableMaps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <li class="px-2 py-1 flex items-center gap-2"
                                x-show="isVisible(<?php echo \Illuminate\Support\Js::from($m['id'])->toHtml() ?>, <?php echo \Illuminate\Support\Js::from($m['name'])->toHtml() ?>)">
                                <input type="checkbox" class="pw-checkbox"
                                       value="<?php echo e($m['id']); ?>" x-model="availSel">
                                <code class="text-[var(--color-primary)] font-semibold text-xs"><?php echo e($m['id']); ?></code>
                                <span class="text-xs truncate flex-1"><?php echo e($m['name']); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <li class="px-3 py-6 text-center text-xs text-[var(--color-text-soft)]">
                                gs.conf not reachable.
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            
            <div class="grid grid-cols-2 gap-2 mt-3">
                <button type="button"
                        class="pw-btn pw-btn-danger"
                        :disabled="onlineSel.length === 0"
                        :class="onlineSel.length === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                        @click="pwConfirm({ title:'Stop Maps', message:`Stop ${onlineSel.length} selected map(s) with delay ${delay}s?`, confirmText:'Stop', confirmStyle:'danger', onConfirm: () => pwMapsAction('stop', onlineSel, delay) })">
                    ■ Stop Selected ( <span x-text="onlineSel.length"></span> )
                </button>
                <button type="button"
                        class="pw-btn pw-btn-success"
                        :disabled="availSel.length === 0"
                        :class="availSel.length === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                        @click="pwConfirm({ title:'Start Maps', message:`Start ${availSel.length} selected map(s)?`, confirmText:'Start', confirmStyle:'success', onConfirm: () => pwMapsAction('start', availSel, delay) })">
                    ▶ Start Selected ( <span x-text="availSel.length"></span> )
                </button>
            </div>

            <div class="mt-2 text-[10px] text-[var(--color-text-soft)]">
                Source: <code><?php echo e($gsConfPath); ?></code>
            </div>
        </div>
    </div>

</div>

<div class="mt-4">
    <?php echo $__env->make('panel._backup-list', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<script>
function pwServicesPanel() {
    return {
        busy: false,
        action: null,
        svcState: {},  // { logservice: {phase, label}, ... }
        progressTotal: 0,
        progressDone: 0,
        currentLabel: '',
        init() {},

        setState(parentKey, phase, label) {
            this.svcState[parentKey] = { phase, label };
            this.svcState = { ...this.svcState };
        },
        clearState(key) {
            delete this.svcState[key];
            this.svcState = { ...this.svcState };
        },

        async runAction(action) {
            if (this.busy) return;
            this.busy = true;
            this.action = action;
            this.svcState = {};
            this.progressDone = 0;
            this.currentLabel = '';
            // Maps precomputed in PHP (see @php block at top of view).
            this._svcLabels = <?php echo json_encode($pwSvcLabels); ?>;
            const stopOrder  = <?php echo json_encode($pwStopOrder); ?>;
            const startOrder = <?php echo json_encode($pwStartOrder); ?>;
            const instMap    = <?php echo json_encode($pwInstMap); ?>;
            if (action === 'stop') {
                this.progressTotal = stopOrder.length;
            } else {
                this.progressTotal = startOrder.reduce((a,k)=>a+(instMap[k]||1),0);
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            let res;
            try {
                res = await fetch('<?php echo e(route("panel.server.control.stream")); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'text/event-stream',
                    },
                    body: 'action=' + encodeURIComponent(action),
                });
            } catch (e) {
                this.busy = false;
                window.pwToast && pwToast('Network error: ' + e.message, 'error');
                return;
            }

            if (!res.ok || !res.body) {
                this.busy = false;
                window.pwToast && pwToast('Failed to start action (HTTP ' + res.status + ')', 'error');
                return;
            }

            const reader = res.body.getReader();
            const decoder = new TextDecoder();
            let buf = '', result = null;

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;
                buf += decoder.decode(value, { stream: true });
                let idx;
                while ((idx = buf.indexOf('\n\n')) >= 0) {
                    const chunk = buf.slice(0, idx);
                    buf = buf.slice(idx + 2);
                    const parsed = this.parseSSE(chunk);
                    if (!parsed) continue;
                    if (parsed.event === 'progress') {
                        this.handleProgress(parsed.data);
                        if (parsed.data.phase === 'done') result = parsed.data;
                    }
                }
            }

            this.busy = false;
            const failed = (result && result.failed) || [];
            if (failed.length === 0) {
                window.pwToast && pwToast(
                    action === 'stop' ? 'Server stopped' : 'Server is online',
                    'success'
                );
            } else {
                window.pwToast && pwToast('Completed with errors: ' + failed.join(', '), 'error');
            }
            // Refresh page biar badge status akurat
            setTimeout(() => window.location.reload(), 1200);
        },

        handleProgress(p) {
            const parent = p.parent || p.key;
            if (!parent) return;
            const isStop = this.action === 'stop';

            if (p.phase === 'start') {
                const inst = (p.key && p.key.includes('#'))
                    ? ` ${p.key.split('#')[1]}/${this.instCount(parent)}`
                    : '';
                this.currentLabel = (this._svcLabels && this._svcLabels[parent]) || parent;
                this.setState(parent, 'running', (isStop ? 'Stopping' : 'Starting') + inst + '…');
            } else if (p.phase === 'ok') {
                this.progressDone++;
                this.setState(parent, 'ok', isStop ? 'Offline' : 'Online');
            } else if (p.phase === 'fail') {
                this.progressDone++;
                this.setState(parent, 'fail', 'Failed');
            }
        },

        instCount(key) {
            const map = <?php echo json_encode($pwInstMap); ?>;
            return map[key] || 1;
        },

        parseSSE(chunk) {
            let event = 'message', data = '';
            for (const line of chunk.split('\n')) {
                if (line.startsWith('event:')) event = line.slice(6).trim();
                else if (line.startsWith('data:')) data += line.slice(5).trim();
            }
            if (!data) return null;
            try { return { event, data: JSON.parse(data) }; } catch { return null; }
        },
    };
}

// Keep existing helpers
function pwPostServer(payload) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = <?php echo json_encode(route('panel.server.control'), 15, 512) ?>;
    const add = (n, v) => {
        const i = document.createElement('input');
        i.type = 'hidden'; i.name = n; i.value = v;
        form.appendChild(i);
    };
    add('_token', token);
    Object.entries(payload).forEach(([k,v]) => {
        if (Array.isArray(v)) v.forEach(x => add(k, x));
        else add(k, v);
    });
    document.body.appendChild(form);
    form.submit();
}
function pwMapsAction(action, ids, delay) {
    if (!ids.length) return;
    pwPostServer({ service: 'gs', action, 'maps[]': ids, delay });
}

function pwBroadcast(message, channel = 9) {
    const tk = document.querySelector('meta[name="csrf-token"]')?.content;
    return fetch(<?php echo json_encode(route('panel.server.broadcast'), 15, 512) ?>, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': tk,
        },
        body: JSON.stringify({ message, channel }),
    }).catch(() => {});
}

function pwMapsPanel() {
    return {
        onlineSel: [],
        availSel:  [],
        q: '',
        delay: 300,
        countdown: 0,
        _timer: null,

        isVisible(id, name) {
            if (!this.q) return true;
            const q = this.q.toLowerCase();
            return id.toLowerCase().includes(q) || name.toLowerCase().includes(q);
        },

        toggleAll(which, ev) {
            const ids = [];
            this.$root.querySelectorAll(`[data-list='${which}'] li`).forEach(li => {
                if (li.style.display === 'none') return;
                const cb = li.querySelector('input[type=checkbox]');
                if (cb) ids.push(cb.value);
            });
            if (which === 'online') this.onlineSel = ev.target.checked ? ids : [];
            if (which === 'avail')  this.availSel  = ev.target.checked ? ids : [];
        },

        fmtLeft(s) {
            const m = Math.floor(s/60), r = s%60;
            if (m > 0 && r > 0) return `${m} minute(s) ${r} second(s)`;
            if (m > 0)          return `${m} minute(s)`;
            return `${r} second(s)`;
        },

        fmtCountdown() {
            const s = this.countdown;
            const m = Math.floor(s / 60);
            const r = s % 60;
            return (m > 0 ? m + 'm ' : '') + r + 's';
        },

        _buildMarks(d) {
            const marks = new Set();
            for (let m = Math.floor(d/60); m >= 1; m--) marks.add(m*60);
            [30, 10, 5, 4, 3, 2, 1].forEach(s => { if (s < d) marks.add(s); });
            return marks;
        },

        startDelayStop() {
            const d = parseInt(this.delay) || 0;
            if (d <= 0) {
                window.pwConfirm({
                    title: 'Stop All Maps',
                    message: 'Delay = 0. Stop all maps immediately?',
                    confirmText: 'Stop Now',
                    confirmStyle: 'danger',
                    onConfirm: () => pwPostServer({ service:'gs', action:'stop' })
                });
                return;
            }
            const self = this;
            window.pwConfirm({
                title: 'Delay Stop',
                message: `Stop all maps (gs) after ${d} second(s)?\n` +
                         `Players will be notified in-game (World chat).\n` +
                         `Countdown runs in this browser — don't close the tab.`,
                confirmText: 'Start Countdown',
                confirmStyle: 'danger',
                onConfirm: () => {
                    self.countdown = d;
                    const marks = self._buildMarks(d);

                    pwBroadcast(`[SYSTEM] Server will shut down in ${self.fmtLeft(d)}. Please log out to avoid losing items.`);

                    self._timer = setInterval(() => {
                        self.countdown--;
                        if (marks.has(self.countdown) && self.countdown > 0) {
                            pwBroadcast(`[SYSTEM] Server shutdown in ${self.fmtLeft(self.countdown)}.`);
                        }
                        if (self.countdown <= 0) {
                            clearInterval(self._timer);
                            self._timer = null;
                            self.countdown = 0;
                            pwBroadcast('[SYSTEM] Server is shutting down now. See you soon!');
                            // Small grace so final broadcast flushes before page navigates
                            setTimeout(() => pwPostServer({ service:'gs', action:'stop' }), 600);
                        }
                    }, 1000);
                }
            });
        },

        cancelDelayStop() {
            if (this._timer) { clearInterval(this._timer); this._timer = null; }
            this.countdown = 0;
            pwBroadcast('[SYSTEM] Scheduled shutdown has been cancelled.');
            window.pwToast && pwToast('Delay stop cancelled', 'info');
        },
    };
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('panel.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/pw-panel/resources/views/panel/server/index.blade.php ENDPATH**/ ?>