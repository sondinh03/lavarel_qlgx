@props([
    'title',
    'description' => null,
    'icon' => null,
    'tone' => 'slate',
    'panel' => true,
])

@php
    $toneClasses = match ($tone) {
        'primary' => ['bg' => 'bg-primary-50/80', 'text' => 'text-primary-600', 'ring' => 'ring-primary-100/60'],
        'emerald' => ['bg' => 'bg-emerald-50/80', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-100/60'],
        default => ['bg' => 'bg-slate-100/80', 'text' => 'text-slate-400', 'ring' => 'ring-slate-200/60'],
    };

    $defaultIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />';
@endphp

@if($panel)
<x-mac-panel {{ $attributes }}>
@endif

    <div class="py-16 px-8 text-center">
        <div class="mx-auto w-14 h-14 rounded-2xl {{ $toneClasses['bg'] }} ring-1 {{ $toneClasses['ring'] }} flex items-center justify-center shadow-mac-sm">
            <svg class="w-7 h-7 {{ $toneClasses['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon ?? $defaultIcon !!}
            </svg>
        </div>

        <p class="mt-5 text-base font-semibold tracking-tight text-slate-600">{{ $title }}</p>

        @if($description)
            <p class="mt-1.5 text-sm text-slate-400 max-w-sm mx-auto leading-relaxed">{{ $description }}</p>
        @endif

        @if(trim($slot))
            <div class="mt-6 flex items-center justify-center gap-2">
                {{ $slot }}
            </div>
        @endif
    </div>

@if($panel)
</x-mac-panel>
@endif
