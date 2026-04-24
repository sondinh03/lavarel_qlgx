{{-- confirm-dialog.blade.php --}}
<div
    x-data="{
        show: false,
        message: '',
        wireMethod: '',

        open(message, wireMethod) {
        console.log('open() called', { message, wireMethod });
            this.message    = message;
            this.wireMethod = wireMethod;
            this.show       = true;
        },

        confirm() {
            console.log('1. confirm() called');
            console.log('2. wireMethod =', this.wireMethod);

            this.show = false;

            if (!this.wireMethod) {
                console.log('3. STOP: wireMethod empty');
                return;
            }

            const match = this.wireMethod.match(/^(\w+)\((.*)\)$/);
            console.log('4. regex match =', match);

            if (!match) {
                console.log('5. STOP: regex no match');
                return;
            }

            const method = match[1];
            const args   = match[2]
                ? match[2].split(',').map(a => {
                    const trimmed = a.trim();
                    return isNaN(trimmed) ? trimmed : Number(trimmed);
                })
                : [];

            console.log('6. method =', method);
            console.log('7. args =', args);

            const componentEl = document.querySelector('[wire\\:id]');
            console.log('8. componentEl =', componentEl);

            if (!componentEl) {
                console.log('9. STOP: no component found');
                return;
            }

            const componentId = componentEl.getAttribute('wire:id');
            console.log('10. componentId =', componentId);

            const component = window.livewire.find(componentId);
            console.log('11. component =', component);

            component.call(method, ...args);
            console.log('12. call() fired');
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
        class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4">

        <button
            @click="close()"
            class="absolute top-3 right-3 p-1 rounded-lg
                text-slate-400 hover:text-slate-600
                hover:bg-slate-100 transition-all"
            type="button"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    
        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-50 mx-auto">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
            </svg>
        </div>

        <div class="text-center">
            <h3 class="text-base font-semibold text-slate-900">Xác nhận</h3>
            <p class="text-sm text-slate-500 mt-1" x-text="message"></p>
        </div>

        <div class="flex gap-3">
            <button
                @click="close()"
                type="button"
                class="flex-1 px-4 py-2 text-sm font-medium text-slate-700
                       bg-slate-100 hover:bg-slate-200 rounded-xl transition-all duration-200">
                Huỷ
            </button>
            <button
                @click="confirm()"
                type="button"
                class="flex-1 px-4 py-2 text-sm font-medium text-white
                       bg-red-600 hover:bg-red-700 rounded-xl transition-all duration-200">
                Xác nhận
            </button>
        </div>
    </div>
</div>