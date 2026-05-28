@props([
    'title',
    'right' => null,
])

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden {{ $attributes->get('class') }}">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
        <h2 class="text-base font-bold text-slate-800">{{ $title }}</h2>
        @if($right !== null)
            <span class="text-xs text-slate-400">{{ $right }}</span>
        @endif
    </div>

    <div class="p-6">
        {{ $slot }}
    </div>
</div>
