@props([
    'id',
    'name' => '',
    'initials' => '',
    'src' => null,
    'wireTarget',
    'enabled' => false,
    'size' => 'md',
    'variant' => 'list',
    'inputPrefix' => 'quick-avatar',
])

@php
    $cameraInputId = $inputPrefix . '-camera-' . $id;
    $galleryInputId = $inputPrefix . '-gallery-' . $id;
@endphp

@if($enabled)
<div
    {{ $attributes->class(['relative inline-flex flex-shrink-0 group']) }}
    wire:key="{{ $inputPrefix }}-wrap-{{ $id }}"
    x-data="{
        open: false,
        menuStyle: '',
        toggle() {
            this.open = !this.open;
            if (!this.open) return;
            const r = this.$refs.trigger.getBoundingClientRect();
            const left = Math.min(Math.max(8, r.left + r.width / 2), window.innerWidth - 8);
            this.menuStyle = `top:${Math.round(r.bottom + 8)}px;left:${Math.round(left)}px;`;
        },
        close() { this.open = false; }
    }"
    @click.stop
    @mousedown.stop
    @keydown.escape.window="close()">
    <x-entity-avatar
        :src="$src"
        :name="$name"
        :initials="$initials"
        :size="$size"
        :variant="$variant" />

    <button
        type="button"
        x-ref="trigger"
        class="absolute inset-0 rounded-full cursor-pointer
            bg-black/0 sm:group-hover:bg-black/35
            flex items-center justify-center transition-colors"
        title="{{ $src ? 'Đổi ảnh' : 'Thêm ảnh' }}"
        @click.stop="toggle()"
        aria-haspopup="menu"
        :aria-expanded="open.toString()">
        <span class="absolute -bottom-0.5 -right-0.5 inline-flex items-center justify-center
            w-5 h-5 rounded-full bg-primary-500 text-white shadow-mac-sm
            ring-2 ring-white
            sm:opacity-90 sm:group-hover:opacity-100">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </span>
        <span
            wire:loading.flex
            wire:target="{{ $wireTarget }}"
            class="absolute inset-0 hidden items-center justify-center rounded-full bg-black/40">
            <svg class="w-4 h-4 text-white animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </span>
    </button>

    <template x-teleport="body">
        <div
            x-show="open"
            x-cloak
            @click.outside="close()"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="fixed z-[80] min-w-[10.5rem] -translate-x-1/2 rounded-xl
                border border-black/[0.08] bg-white shadow-mac-md py-1 overflow-hidden"
            :style="menuStyle"
            role="menu">
            <label
                for="{{ $cameraInputId }}"
                class="flex items-center gap-2.5 px-3 py-2.5 text-sm text-slate-700
                    hover:bg-primary-50 cursor-pointer select-none"
                role="menuitem"
                @click="close()">
                <svg class="w-4 h-4 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Chụp ảnh
            </label>
            <label
                for="{{ $galleryInputId }}"
                class="flex items-center gap-2.5 px-3 py-2.5 text-sm text-slate-700
                    hover:bg-primary-50 cursor-pointer select-none"
                role="menuitem"
                @click="close()">
                <svg class="w-4 h-4 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Thư viện
            </label>
        </div>
    </template>

    <input
        id="{{ $cameraInputId }}"
        type="file"
        accept="image/*"
        capture="environment"
        class="hidden"
        wire:model="{{ $wireTarget }}" />

    <input
        id="{{ $galleryInputId }}"
        type="file"
        accept="image/*"
        class="hidden"
        wire:model="{{ $wireTarget }}" />
</div>
@else
    <x-entity-avatar
        :src="$src"
        :name="$name"
        :initials="$initials"
        :size="$size"
        :variant="$variant" />
@endif
