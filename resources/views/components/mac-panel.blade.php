@props([
    'padding' => false,
    'overflow' => false,
])

@php
    $classes = collect([
        'bg-white/75 backdrop-blur-xl rounded-2xl border border-black/[0.06] shadow-mac',
        $padding ? 'p-4 lg:p-6' : '',
        $overflow ? 'overflow-hidden' : '',
    ])->filter()->implode(' ');
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
