{{-- Global confirmation modal.
     Usage:
         window.pwConfirm({
             title: 'Stop Server',
             message: 'Are you sure you want to stop all services?',
             confirmText: 'Stop',
             confirmStyle: 'danger',   // primary | success | danger | warn
             onConfirm: () => { ... },
         });
     Or shorthand: window.pwConfirm('Are you sure?', () => fn());
--}}
<div
    id="pw-confirm-root"
    x-data="{
        open: false,
        title: 'Confirmation',
        message: '',
        confirmText: 'Confirm',
        cancelText:  'Cancel',
        confirmStyle: 'primary',
        _cb: null,
        show(opts) {
            opts = opts || {};
            this.title        = opts.title        || 'Confirmation';
            this.message      = opts.message      || '';
            this.confirmText  = opts.confirmText  || 'Confirm';
            this.cancelText   = opts.cancelText   || 'Cancel';
            this.confirmStyle = opts.confirmStyle || 'primary';
            this._cb          = typeof opts.onConfirm === 'function' ? opts.onConfirm : null;
            this.open = true;
        },
        close() { this.open = false; this._cb = null; },
        confirm() {
            const cb = this._cb;
            this.open = false;
            this._cb  = null;
            if (cb) cb();
        },
        init() {
            window.pwConfirm = (a, b) => {
                if (typeof a === 'string') this.show({ message: a, onConfirm: b });
                else this.show(a || {});
            };
        }
    }"
    x-cloak
    x-show="open"
    @keydown.escape.window="close()"
    class="fixed inset-0 z-[110] flex items-center justify-center"
>
    <div class="absolute inset-0 bg-slate-900/40" @click="close()"></div>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="relative w-[90%] max-w-md bg-white rounded-lg shadow-xl border border-[var(--color-border-2)] overflow-hidden"
    >
        <div class="px-4 py-3 border-b border-[var(--color-border-2)] flex items-center justify-between">
            <h3 class="font-semibold text-[var(--color-text)]" x-text="title"></h3>
            <button type="button" class="text-slate-400 hover:text-slate-700 text-xl leading-none" @click="close()">×</button>
        </div>
        <div class="px-4 py-4 text-sm text-[var(--color-text)] whitespace-pre-line" x-text="message"></div>
        <div class="px-4 py-3 bg-[var(--color-surface-2,#f7f9fb)] border-t border-[var(--color-border-2)] flex justify-end gap-2">
            <button type="button" class="pw-btn min-w-[96px]" @click="close()" x-text="cancelText"></button>
            <button type="button"
                    class="pw-btn min-w-[96px] font-semibold"
                    :class="{
                        'pw-btn-primary': confirmStyle === 'primary',
                        'pw-btn-success': confirmStyle === 'success',
                        'pw-btn-danger':  confirmStyle === 'danger',
                        'pw-btn-warn':    confirmStyle === 'warn',
                    }"
                    @click="confirm()"
                    x-text="confirmText"></button>
        </div>
    </div>
</div>
<style>[x-cloak]{display:none!important}</style>
