@props([
    'label'   => '',
    'icon'    => null,
    'align'   => 'right',
    'width'   => '48',
    'variant' => 'outline',
])

<div x-data="{ open: false }" class="relative">

    <x-button
        :variant="$variant"
        @click="open = !open"
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

    <div
        x-show="open"
        @click.away="open = false"
        @keydown.escape.window="open = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute {{ $align === 'right' ? 'right-0' : 'left-0' }} mt-2 w-{{ $width }}
               bg-white/85 backdrop-blur-xl rounded-xl border border-black/[0.06] shadow-mac py-1.5 z-50"
        style="display:none"
        role="menu"
    >
        {{ $slot }}
    </div>

</div>
