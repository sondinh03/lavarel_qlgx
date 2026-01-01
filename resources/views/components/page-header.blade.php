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
    'right'  => 'text-right',
    default  => 'text-left'
};

$justifyClass = match($align) {
    'center' => 'justify-center',
    'right'  => 'justify-end',
    default  => 'justify-start'
};
@endphp

<th {{ $attributes->merge([
    'class' => "px-6 py-4 {$alignClass} text-xs font-bold text-slate-600 uppercase tracking-wider"
]) }}>
    @if($sortable && $sortField)
        <button
            wire:click="sortBy('{{ $sortField }}')"
            class="group flex items-center gap-2 w-full {{ $justifyClass }}
                   hover:text-slate-900 transition-colors">

            <span>{{ $slot }}</span>

            @if($currentSort === $sortField)
                {{-- Active Sort Icon --}}
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($sortDirection === 'asc')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 15l7-7 7 7" />
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 9l-7 7-7-7" />
                    @endif
                </svg>
            @else
                {{-- Inactive Sort Icon --}}
                <svg class="w-4 h-4 text-slate-400 opacity-0
                            group-hover:opacity-100 transition-opacity"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 16V4m0 0L3 8m4-4l4 4
                             m6 0v12m0 0l4-4m-4 4l-4-4" />
                </svg>
            @endif
        </button>
    @else
        {{ $slot }}
    @endif
</th>
