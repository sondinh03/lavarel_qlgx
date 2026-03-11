@props([
    'wire' => null,
    'icon' => 'edit',
    'color' => 'primary',
    'loading' => false,
    'confirm' => null,
    'debounce' => null,
])

@php
$colorClass = match($color) {
    'primary' => 'text-primary-600 hover:text-primary-700',
    'danger'  => 'text-red-600 hover:text-red-800',
    'warning' => 'text-orange-600 hover:text-orange-700',
    'success' => 'text-emerald-600 hover:text-emerald-700',
    'info'    => 'text-blue-600 hover:text-blue-700',
    default   => 'text-slate-600 hover:text-slate-700',
};

$wireClickAttr = 'wire:click';
if ($debounce && $wire) {
    $wireClickAttr = 'wire:click.debounce.' . $debounce . 'ms';
}
@endphp

<button
    {{ $attributes->merge([
        'class' => "inline-flex items-center gap-1 text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed $colorClass"
    ]) }}
    @if($wire)
        @if($confirm)
            x-data
            @click="if(confirm('{{ $confirm }}')) $wire.{{ $wire }}"
        @else
            {{ $wireClickAttr }}="{{ $wire }}"
        @endif
    @endif
    @if($loading && $wire) wire:loading.attr="disabled" wire:target="{{ $wire }}" @endif
    type="button">

    {{-- Loading Spinner --}}
    @if($loading && $wire)
    <svg wire:loading wire:target="{{ $wire }}" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    @endif

    {{-- Icon --}}
    @if($icon)
    <svg
        @if($loading && $wire) wire:loading.remove wire:target="{{ $wire }}" @endif
        class="w-4 h-4"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24">
        @switch($icon)
            @case('edit')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                @break
            @case('trash')
            @case('delete')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                @break
            @case('check')
            @case('activate')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                @break
            @case('archive')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                @break
            @case('eye')
            @case('view')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                @break
            @case('download')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                @break
            @case('refresh')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                @break
            @case('lock')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                @break
            @case('unlock')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                @break
            @case('copy')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                @break
        @endswitch
    </svg>
    @endif

    {{-- Label --}}
    <span>{{ $slot }}</span>
</button>