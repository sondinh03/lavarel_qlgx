@props([
    'variant' => 'primary',
    'size' => 'md',
    'as' => 'button',
    'disabled' => false,
    'wire' => null,
    'confirm' => null,
])

@php
$base = "inline-flex items-center justify-center gap-2 font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 active:scale-[0.98]";

$sizeClass = match($size) {
    'sm' => 'px-3 py-2 text-sm rounded-lg',
    default => 'px-4 py-2.5 text-sm rounded-xl',
};

$variantClass = match($variant) {
    'primary' => 'bg-primary-500 text-white hover:bg-primary-600 focus-visible:ring-primary-500 shadow-mac-sm',
    'outline' => 'bg-white/80 text-slate-700 border border-black/[0.08] hover:bg-white focus-visible:ring-primary-500/40 shadow-mac-sm',
    'ghost'   => 'text-slate-600 hover:bg-black/[0.04] focus-visible:ring-primary-500/40',
    'subtle'  => 'bg-slate-100/80 text-slate-600 hover:bg-slate-200/80 focus-visible:ring-slate-300',
    'danger'  => 'bg-red-50/90 text-red-600 border border-red-200/80 hover:bg-red-100 focus-visible:ring-red-200',
    'warning' => 'bg-orange-50/90 text-orange-600 border border-orange-200/80 hover:bg-orange-100 focus-visible:ring-orange-200',
    'success' => 'bg-green-50/90 text-green-700 border border-green-200/80 hover:bg-green-100 focus-visible:ring-green-200',
    'info'    => 'bg-blue-50/90 text-blue-600 border border-blue-200/80 hover:bg-blue-100 focus-visible:ring-blue-200',
    default   => 'bg-primary-500 text-white hover:bg-primary-600 focus-visible:ring-primary-500 shadow-mac-sm',
};

$disabledClass = "disabled:opacity-40 disabled:cursor-not-allowed disabled:active:scale-100";
@endphp

@if($as === 'a')
    <a {{ $attributes->merge(['class' => "$base $sizeClass $variantClass $disabledClass"]) }}>
        {{ $slot }}
    </a>

@else
    <button
        {{ $attributes->merge([
            'type' => 'button',
            'class' => "$base $sizeClass $variantClass $disabledClass"
        ]) }}
        @if($confirm && $wire)
            @click="$dispatch('open-confirm', {
                message: {{ Js::from($confirm) }},
                wireMethod: {{ Js::from($wire) }},
                componentId: ($el.closest('[wire\\:id]') || {}).getAttribute ? $el.closest('[wire\\:id]').getAttribute('wire:id') : null
            })"
        @elseif($wire)
            wire:click="{{ $wire }}"
        @endif
        @disabled($disabled)>
        {{ $slot }}
    </button>
@endif
