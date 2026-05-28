@props([
    'title',
    'description' => null,
    'icon' => null, // SVG path d or full svg slot
    'tone' => 'slate', // slate | primary | emerald
])

@php
    $toneClasses = match ($tone) {
        'primary' => ['bg' => 'bg-primary-50', 'text' => 'text-primary-600'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
        default => ['bg' => 'bg-slate-100', 'text' => 'text-slate-400'],
    };
@endphp

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-16 text-center">
    <div class="mx-auto w-16 h-16 rounded-2xl {{ $toneClasses['bg'] }} flex items-center justify-center">
        <svg class="w-8 h-8 {{ $toneClasses['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $icon ?? '' !!}
        </svg>
    </div>

    <p class="mt-4 text-lg font-semibold text-slate-400">{{ $title }}</p>

    @if($description)
        <p class="mt-1 text-sm text-slate-400">{{ $description }}</p>
    @endif

    {{ $slot }}
</div>
