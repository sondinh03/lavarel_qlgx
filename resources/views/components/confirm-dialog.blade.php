{{-- confirm-dialog.blade.php --}}
@once
<script>
    window.__confirmDialog = function () {
        return {
            show: false,
            message: '',
            wireMethod: '',
            componentId: null,

            open(event) {
                const detail = event.detail || {};
                this.message = detail.message || '';
                this.wireMethod = detail.wireMethod || '';
                this.componentId = detail.componentId
                    || (event.target && typeof event.target.closest === 'function'
                        ? (event.target.closest('[wire\\:id]') || {}).getAttribute?.('wire:id')
                        : null)
                    || null;
                this.show = true;
            },

            resolveComponent() {
                const livewire = window.livewire || window.Livewire;
                if (!livewire || typeof livewire.find !== 'function') {
                    return null;
                }

                if (this.componentId) {
                    const byId = livewire.find(this.componentId);
                    if (byId) {
                        return byId;
                    }
                }

                const els = Array.from(document.querySelectorAll('[wire\\:id]'));
                for (const el of els) {
                    if (el.closest('[data-layout-livewire="notification-bell"]') || el.closest('header')) {
                        continue;
                    }
                    const id = el.getAttribute('wire:id');
                    if (!id) {
                        continue;
                    }
                    const component = livewire.find(id);
                    if (component) {
                        return component;
                    }
                }

                const first = els.find(function (el) {
                    return !el.closest('[data-layout-livewire="notification-bell"]');
                });
                const firstId = first ? first.getAttribute('wire:id') : null;
                return firstId ? livewire.find(firstId) : null;
            },

            confirm() {
                this.show = false;

                if (!this.wireMethod) {
                    return;
                }

                const match = this.wireMethod.match(/^(\w+)(?:\((.*)\))?$/);
                if (!match) {
                    return;
                }

                const method = match[1];
                const args = match[2]
                    ? match[2].split(',').map(function (a) {
                        const trimmed = a.trim().replace(/^['"]|['"]$/g, '');
                        if (trimmed !== '' && /^-?\d+(\.\d+)?$/.test(trimmed)) {
                            return Number(trimmed);
                        }
                        return trimmed;
                    })
                    : [];

                const component = this.resolveComponent();
                if (!component || typeof component.call !== 'function') {
                    console.error('confirm-dialog: không tìm thấy Livewire component để gọi', this.wireMethod);
                    return;
                }

                component.call(method, ...args);
            },

            close() {
                this.show = false;
                this.message = '';
                this.wireMethod = '';
                this.componentId = null;
            },
        };
    };
</script>
@endonce

<div
    x-data="window.__confirmDialog()"
    x-on:open-confirm.window="open($event)"
    x-show="show"
    x-cloak
    class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-[60] p-4"
    role="dialog"
    aria-modal="true"
    @click.self="close()"
    @keydown.escape.window="close()"
>
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative bg-white/90 backdrop-blur-xl rounded-2xl border border-black/[0.06] shadow-mac
            w-full max-w-sm p-6 space-y-4"
        @click.stop
    >
        <button
            type="button"
            @click="close()"
            class="absolute top-3 right-3 p-1.5 rounded-lg
                text-slate-400 hover:text-slate-600
                hover:bg-black/[0.04] transition-all"
            aria-label="Đóng"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-red-50/90 ring-1 ring-red-100/80 mx-auto shadow-mac-sm">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
            </svg>
        </div>

        <div class="text-center">
            <h3 class="text-base font-semibold tracking-tight text-slate-900">Xác nhận</h3>
            <p class="text-sm text-slate-500 mt-1" x-text="message"></p>
        </div>

        <div class="flex gap-3 pt-1">
            <button
                type="button"
                @click="close()"
                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl
                    bg-white/80 text-slate-700 border border-black/[0.08] hover:bg-white
                    shadow-mac-sm transition-all active:scale-[0.98]"
            >
                Huỷ
            </button>
            <button
                type="button"
                @click="confirm()"
                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl
                    bg-red-50/90 text-red-600 border border-red-200/80 hover:bg-red-100
                    shadow-mac-sm transition-all active:scale-[0.98]"
            >
                Xác nhận
            </button>
        </div>
    </div>
</div>
