@props([
    'label'   => '',
    'icon'     => null,
    'align'    => 'right',   {{-- 'left' | 'right' --}}
    'width'    => '48',      {{-- Tailwind width: '48' | '56' | '64' --}}
    'variant'  => 'outline', {{-- truyền thẳng vào x-button --}}
])

<div
    x-data="{
        open: false,
        triggerRect: {},
        updatePosition() {
            const el = this.$refs.trigger.getBoundingClientRect();
            this.triggerRect = {
                top:    el.bottom + window.scrollY,
                left:   el.left   + window.scrollX,
                right:  window.innerWidth - el.right + window.scrollX,
                width:  el.width,
            };
        }
    }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
>
    {{-- Trigger --}}
    <div x-ref="trigger">
        <x-button
            :variant="$variant"
            @click="updatePosition(); open = !open"
            :aria-expanded="'false'"
            x-bind:aria-expanded="open.toString()"
            aria-haspopup="true"
        >
            @if($icon)
                <x-icon :name="$icon" class="w-4 h-4" />
            @endif

            @if($label)
                {{ $label }}
                <svg class="w-3.5 h-3.5 transition-transform duration-150"
                    :class="{ 'rotate-180': open }"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            @endif
        </x-button>
    </div>

    {{-- Dropdown — teleport ra body để thoát overflow:hidden --}}
    <template x-teleport="body">
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"

            {{-- Vị trí tính từ triggerRect --}}
            :style="
                '{{ $align }}' === 'right'
                    ? `position:absolute; top:${triggerRect.top + 4}px; right:${triggerRect.right}px; z-index:9999;`
                    : `position:absolute; top:${triggerRect.top + 4}px; left:${triggerRect.left}px; z-index:9999;`
            "

            class="w-{{ $width }} bg-white rounded-xl shadow-lg border border-slate-200 py-1 focus:outline-none"
            role="menu"
            style="display:none"
        >
            {{ $slot }}
        </div>
    </template>
</div>