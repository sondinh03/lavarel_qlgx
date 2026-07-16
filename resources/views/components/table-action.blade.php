@props([
'wire' => null,
'icon' => 'edit',
'color' => 'primary',
'loading' => false,
'confirm' => null,
'debounce' => null,
])

@php
$colorClass = match($color) {
'primary' => 'text-primary-600 hover:text-primary-700',
'danger' => 'text-red-600 hover:text-red-800',
'warning' => 'text-orange-600 hover:text-orange-700',
'success' => 'text-emerald-600 hover:text-emerald-700',
'info' => 'text-blue-600 hover:text-blue-700',
default => 'text-slate-600 hover:text-slate-700',
};

$wireClickAttr = 'wire:click';
if ($debounce && $wire) {
$wireClickAttr = 'wire:click.debounce.' . $debounce . 'ms';
}
@endphp

<span class="inline-flex">

    <button
        {{ $attributes->merge([
            'class' => "inline-flex items-center gap-1 text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed $colorClass"
        ]) }}
        @if($wire)
            @if($confirm)
                @click="$dispatch('open-confirm', {
                    message: {{ Js::from($confirm) }},
                    wireMethod: {{ Js::from($wire) }},
                    componentId: ($el.closest('[wire\\:id]') || {}).getAttribute ? $el.closest('[wire\\:id]').getAttribute('wire:id') : null
                })"
            @else
                {{ $wireClickAttr }}="{{ $wire }}"
            @endif
        @endif
        @if($loading && $wire) wire:loading.attr="disabled" wire:target="{{ $wire }}" @endif
        type="button">

        @if($loading && $wire)
        <svg wire:loading wire:target="{{ $wire }}" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        @endif

        @if($icon)
        <span @if($loading && $wire) wire:loading.remove wire:target="{{ $wire }}" @endif>
            <x-icon :name="$icon" class="w-4 h-4" />
        </span>
        @endif

        <span>{{ $slot }}</span>
    </button>

</span>