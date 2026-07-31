@props([
    'variant' => 'primary',
    'size' => 'md',
    'as' => 'button',
    'disabled' => false,
    'wire' => null,
    'confirm' => null,
])

@php
$base = "inline-flex items-center justify-center gap-2 font-medium transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 active:scale-[0.98]";

$sizeClass = match($size) {
    'sm' => 'px-3 py-2 text-sm rounded-xl',
    default => 'px-4 py-2 text-sm rounded-xl',
};

$variantClass = match($variant) {
    'primary'   => 'bg-primary-500 text-white hover:bg-primary-600 focus-visible:ring-primary-500 shadow-mac-sm',
    'secondary' => 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus-visible:ring-slate-300',
    // Giữ alias cũ để tương thích
    'outline'   => 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus-visible:ring-slate-300',
    'subtle'    => 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus-visible:ring-slate-300',
    'ghost'     => 'text-slate-600 hover:bg-black/[0.04] focus-visible:ring-primary-500/40',
    'danger'    => 'bg-red-500 text-white hover:bg-red-600 focus-visible:ring-red-500 shadow-mac-sm',
    'warning'   => 'bg-orange-50/90 text-orange-600 border border-orange-200/80 hover:bg-orange-100 focus-visible:ring-orange-200',
    'success'   => 'bg-emerald-50/90 text-emerald-700 border border-emerald-200/80 hover:bg-emerald-100 focus-visible:ring-emerald-200',
    'info'      => 'bg-blue-50/90 text-blue-600 border border-blue-200/80 hover:bg-blue-100 focus-visible:ring-blue-200',
    default     => 'bg-primary-500 text-white hover:bg-primary-600 focus-visible:ring-primary-500 shadow-mac-sm',
};

$disabledClass = "disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100";
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
