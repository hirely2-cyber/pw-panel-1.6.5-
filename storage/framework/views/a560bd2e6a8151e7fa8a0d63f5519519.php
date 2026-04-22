
<div
    id="pw-toast-root"
    x-data="{
        items: [],
        add(msg, level) {
            const id = Date.now() + Math.random();
            this.items.push({ id, msg, level: level || 'info' });
            setTimeout(() => this.remove(id), 4200);
        },
        remove(id) { this.items = this.items.filter(t => t.id !== id); },
        init() { window.pwToast = (m,l) => this.add(m,l); window.dispatchEvent(new CustomEvent('pw-toast-ready')); }
    }"
    class="fixed top-4 right-4 z-[100] flex flex-col gap-2 w-[320px] pointer-events-none"
>
    <template x-for="t in items" :key="t.id">
        <div
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto rounded shadow px-3 py-2 text-[12px] leading-snug flex items-start gap-2 text-white"
            :class="{
                'bg-emerald-600': t.level === 'success',
                'bg-rose-600':    t.level === 'error',
                'bg-amber-500':   t.level === 'warn',
                'bg-slate-700':   t.level === 'info',
            }"
        >
            <span class="font-bold mt-[1px] text-[12px]" x-text="{success:'✓',error:'✕',warn:'!',info:'i'}[t.level]"></span>
            <span class="flex-1 break-words" x-text="t.msg"></span>
            <button type="button" class="text-white/80 hover:text-white text-base leading-none" @click="remove(t.id)">×</button>
        </div>
    </template>
</div>


<?php
    $_ok  = session('ok');
    $_err = session('error');
?>
<?php if($_ok || $_err || ($errors ?? null)?->any()): ?>
    <script>
        (function(){
            const fire = () => {
                <?php if($_ok): ?>  window.pwToast(<?php echo json_encode($_ok, 15, 512) ?>, 'success'); <?php endif; ?>
                <?php if($_err): ?> window.pwToast(<?php echo json_encode($_err, 15, 512) ?>, 'error');  <?php endif; ?>
                <?php if(isset($errors) && $errors->any()): ?>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        window.pwToast(<?php echo json_encode($e, 15, 512) ?>, 'error');
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            };
            if (window.pwToast) fire();
            else window.addEventListener('pw-toast-ready', fire, { once: true });
        })();
    </script>
<?php endif; ?>
<?php /**PATH /var/www/html/pw-panel/resources/views/panel/_toasts.blade.php ENDPATH**/ ?>