@props([
    'title',
    'right' => null,
])

<div {{ $attributes->merge([
    'class' => 'bg-white/75 backdrop-blur-xl rounded-2xl border border-black/[0.06] shadow-mac overflow-hidden',
]) }}>
    <div class="px-6 py-4 mac-hairline-b flex items-center justify-between gap-3 bg-white/30">
        <h2 class="text-base font-semibold tracking-tight text-slate-900">{{ $title }}</h2>
        @if($right !== null)
            <span class="text-xs text-slate-400">{{ $right }}</span>
        @endif
    </div>

    <div class="p-6">
        {{ $slot }}
    </div>
</div>
