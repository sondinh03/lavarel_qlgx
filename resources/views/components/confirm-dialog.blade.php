{{-- confirm-dialog.blade.php --}}
<div
    x-data="{
        show: false,
        message: '',
        wireMethod: '',

        open(message, wireMethod) {
            this.message    = message;
            this.wireMethod = wireMethod;
            this.show       = true;
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
            const args   = match[2]
                ? match[2].split(',').map(a => {
                    const trimmed = a.trim();
                    return isNaN(trimmed) ? trimmed : Number(trimmed);
                })
                : [];

            const componentEl = document.querySelector('[wire\\:id]');

            if (!componentEl) {
                return;
            }

            const componentId = componentEl.getAttribute('wire:id');
            const component = window.livewire.find(componentId);

            component.call(method, ...args);
        },

        close() {
            this.show       = false;
            this.message    = '';
            this.wireMethod = '';
        }
    }"
    x-on:open-confirm.window="open($event.detail.message, $event.detail.wireMethod)"
    x-show="show"
    x-cloak
    class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-[60] p-4"
    role="dialog"
    aria-modal="true"
    @click.self="close()"
    @keydown.escape.window="close()">

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
        @click.stop>

        <button
            @click="close()"
            class="absolute top-3 right-3 p-1.5 rounded-lg
                text-slate-400 hover:text-slate-600
                hover:bg-black/[0.04] transition-all"
            type="button"
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
            <x-button type="button" variant="outline" class="flex-1" @click="close()">
                Huỷ
            </x-button>
            <x-button type="button" variant="danger" class="flex-1" @click="confirm()">
                Xác nhận
            </x-button>
        </div>
    </div>
</div>
