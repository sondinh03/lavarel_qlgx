@props([
'actionLabel' => null,
'actionDisabled' => false,
])

<div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
    <div class="flex items-center justify-between gap-4">

        {{-- LEFT: Filters --}}
        <div class="flex items-center gap-3">
            {{ $slot }}
        </div>

        {{-- RIGHT: Primary Action --}}
        @if($actionLabel)
        <button
            {{ $attributes->whereStartsWith('wire:') }}
            @disabled($actionDisabled)
            class="inline-flex items-center gap-2
                   px-5 py-2.5 rounded-xl
                   bg-gradient-to-r from-primary-500 to-primary-600
                   hover:from-primary-600 hover:to-primary-700
                   text-white text-sm font-semibold
                   active:scale-[0.98]
                   disabled:bg-slate-300 disabled:cursor-not-allowed
                   transition-all shadow-sm">

            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4v16m8-8H4" />
            </svg>

            {{ $actionLabel }}
        </button>
        @endif

    </div>
</div>