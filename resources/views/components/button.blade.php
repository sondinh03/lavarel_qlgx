@props([
    'variant' => 'primary', // primary | outline | ghost | subtle | danger
    'size' => 'md',         // sm | md
    'as' => 'button',       // button | a
    'href' => null,
    'disabled' => false,
])

@php
// Base
$base = "inline-flex items-center justify-center gap-2 font-semibold transition-all focus:outline-none active:scale-95";

// Size
$sizeClass = match($size) {
    'sm' => 'px-3 py-2 text-sm rounded-lg',
    default => 'px-4 py-2.5 text-sm rounded-xl',
};

// Variant
$variantClass = match($variant) {
    'primary' => 'bg-primary-500 text-white hover:bg-primary-600 focus:ring-2 focus:ring-primary-500',
    'outline' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:ring-2 focus:ring-primary-500',
    'ghost' => 'text-slate-600 hover:bg-slate-100 focus:ring-2 focus:ring-primary-500',
    'subtle' => 'bg-slate-100 text-slate-500 hover:bg-slate-200',
    'danger' => 'bg-red-500 text-white hover:bg-red-600 focus:ring-2 focus:ring-red-500',
};

// Disabled
$disabledClass = "disabled:opacity-40 disabled:cursor-not-allowed disabled:active:scale-100";
@endphp

@if($as === 'a')
    <a 
        href="{{ $href }}"
        {{ $attributes->merge([
            'class' => "$base $sizeClass $variantClass"
        ]) }}
    >
        {{ $slot }}
    </a>
@else
    <button
        {{ $attributes->merge([
            'type' => 'button',
            'class' => "$base $sizeClass $variantClass $disabledClass"
        ]) }}
        @disabled($disabled)
    >
        {{ $slot }}
    </button>
@endif