@props([
    'label',
    'value' => null,
    'suffix' => null,
    'subline' => null,
    'valueClass' => 'text-slate-800',
])

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col gap-1">
    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wide">{{ $label }}</div>

    <div class="text-3xl font-extrabold {{ $valueClass }}">
        {{ $value ?? '—' }}@if($suffix)<span class="text-lg font-semibold text-slate-400">{{ $suffix }}</span>@endif
    </div>

    @if($subline)
        <div class="text-xs text-slate-400">{{ $subline }}</div>
    @endif

    {{ $slot }}
</div>
