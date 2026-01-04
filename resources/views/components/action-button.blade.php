@props([
'wire' => null,
'disabled' => false,
'loading' => false,
'icon' => null, // 'plus', 'edit', 'trash', 'check', 'save'
'variant' => 'primary', // primary, secondary, danger
])

<button
    {{ $attributes->merge([
        'class' => match($variant) {
            'primary' => 'bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white',
            'secondary' => 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-100',
            'danger' => 'bg-red-600 hover:bg-rare-700 text-white',
        } . ' inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed transition-all shadow-sm'
    ]) }}
    @if($wire) wire:click="{{ $wire }}" @endif
    @if($loading) wire:loading.attr="disabled" @endif
    @disabled($disabled)>

    {{-- Icon --}}
    @if($icon && !$loading)
    @if($wire)
    <svg wire:loading.remove wire:target="{{ $wire }}" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        @switch($icon)
        @case('plus')
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        @break
        @case('edit')
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        @break
        @case('trash')
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        @break
        @case('check')
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M5 13l4 4L19 7" />
        @break
        @endswitch
    </svg>
    @else
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        @switch($icon)
        @case('plus')
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        @break
        @case('edit')
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        @break
        @case('trash')
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        @break
        @case('check')
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M5 13l4 4L19 7" />
        @break
        @endswitch
    </svg>
    @endif
    @endif

    {{ $slot }}
</button>