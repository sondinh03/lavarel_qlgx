@props([
'sortable' => false,
'sortField' => '',
'currentSort' => '',
'sortDirection' => 'asc',
'align' => 'left'
])

@php
$alignClass = match($align) {
'center' => 'text-center',
'right' => 'text-right',
default => 'text-left'
};

$justifyClass = match($align) {
'center' => 'justify-center',
'right' => 'justify-end',
default => 'justify-start'
};
@endphp

<th {{ $attributes->merge([
    'class' => "px-4 py-3 {$alignClass} text-xs font-semibold text-slate-600"
]) }}>
    @if($sortable && $sortField)
    <button
        wire:click="sortBy('{{ $sortField }}')"
        class="flex items-center h-full gap-2 p w-full {{ $justifyClass }}
           transition-all duration-200
           hover:text-slate-800 hover:opacity-90
           focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 rounded">

        <span class="uppercase tracking-wide">{{ $slot }}</span>

        {{-- Sort Icon --}}
        @if($currentSort === $sortField)
        <svg class="w-3.5 h-3.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            @if($sortDirection === 'asc')
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
            @else
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            @endif
        </svg>
        @else
        <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
        </svg>
        @endif

    </button>
    @else
        <span class="uppercase tracking-wide">
            {{ $slot }}
        </span>
    @endif
</th>