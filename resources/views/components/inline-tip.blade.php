{{-- Tip / gợi ý ngắn trên trang — thống nhất style --}}
@props([
    'tone' => 'primary', // primary | amber | slate
])

@php
    $toneClasses = match ($tone) {
        'amber' => 'bg-amber-50/80 border-amber-200/80 text-amber-900',
        'slate' => 'bg-slate-50 border-black/[0.06] text-slate-600',
        default => 'bg-primary-50/80 border-primary-100 text-primary-800',
    };
    $iconClasses = match ($tone) {
        'amber' => 'text-amber-500',
        'slate' => 'text-slate-400',
        default => 'text-primary-500',
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-xl border px-3 py-2.5 text-xs leading-relaxed {$toneClasses}"]) }}>
    <div class="flex items-start gap-2.5">
        <svg class="w-4 h-4 flex-shrink-0 mt-0.5 {{ $iconClasses }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="min-w-0 flex-1">
            {{ $slot }}
        </div>
    </div>
</div>
