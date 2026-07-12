@props([
    'label',
    'value' => null,
    'suffix' => null,
    'subline' => null,
    'valueClass' => 'text-slate-800',
])

<div {{ $attributes->merge([
    'class' => 'bg-white/75 backdrop-blur-xl rounded-2xl border border-black/[0.06] shadow-mac p-5 flex flex-col gap-1',
]) }}>
    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $label }}</div>

    <div class="text-3xl font-extrabold tracking-tight {{ $valueClass }}">
        {{ $value ?? '—' }}@if($suffix)<span class="text-lg font-semibold text-slate-400">{{ $suffix }}</span>@endif
    </div>

    @if($subline)
        <div class="text-xs text-slate-400">{{ $subline }}</div>
    @endif

    {{ $slot }}
</div>
