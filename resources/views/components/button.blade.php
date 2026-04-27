@props([
    'variant' => 'primary',
    'size' => 'md',
    'as' => 'button',
    'disabled' => false,
    'wire' => null,
    'confirm' => null,
])

@php
$base = "inline-flex items-center justify-center gap-2 font-semibold transition-all focus:outline-none active:scale-95";

$sizeClass = match($size) {
    'sm' => 'px-3 py-2 text-sm rounded-lg',
    default => 'px-4 py-2.5 text-sm rounded-xl',
};

$variantClass = match($variant) {
    'primary' => 'bg-primary-500 text-white hover:bg-primary-600 focus:ring-2 focus:ring-primary-500',
    'outline' => 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50 focus:ring-2 focus:ring-primary-500',
    'ghost'   => 'text-slate-600 hover:bg-slate-100 focus:ring-2 focus:ring-primary-500',
    'subtle'  => 'bg-slate-100 text-slate-500 hover:bg-slate-200',
    'danger'  => 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 focus:ring-2 focus:ring-red-200',
    'warning' => 'bg-orange-50 text-orange-600 border border-orange-200 hover:bg-orange-100 focus:ring-2 focus:ring-orange-200',
    'success' => 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 focus:ring-2 focus:ring-green-200',
    'info'    => 'bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-100 focus:ring-2 focus:ring-blue-200',
    default   => 'bg-primary-500 text-white hover:bg-primary-600 focus:ring-2 focus:ring-primary-500',
};

$disabledClass = "disabled:opacity-40 disabled:cursor-not-allowed disabled:active:scale-100";
@endphp

@if($as === 'a')
    <a {{ $attributes->merge(['class' => "$base $sizeClass $variantClass"]) }}>
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
                wireMethod: {{ Js::from($wire) }}
            })"
        @elseif($wire)
            wire:click="{{ $wire }}"
        @endif
        @disabled($disabled)>
        {{ $slot }}
    </button>
@endif