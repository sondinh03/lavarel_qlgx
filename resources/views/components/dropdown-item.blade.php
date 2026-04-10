@props([
    'as'       => 'button',
    'href'     => null,
    'icon'     => null,
    'disabled' => false,
    'badge'    => null,
])

@php
    $base     = 'w-full flex items-center gap-2.5 px-4 py-2 text-sm text-left transition-colors';
    $active   = 'text-slate-700 hover:bg-slate-50 cursor-pointer';
    $inactive = 'text-slate-400 cursor-not-allowed pointer-events-none';
    $classes  = $base . ' ' . ($disabled ? $inactive : $active);

    $callerClick    = $attributes->get('x-on:click', '');
    $callerClick    = str_replace('$wire.', 'Livewire.find(wireId).', $callerClick);

    $finalClick     = $callerClick
                        ? $callerClick . "; \$dispatch('dropdown-close')"
                        : "\$dispatch('dropdown-close')";
    $restAttributes = $attributes->except('x-on:click')->merge(['class' => $classes]);
@endphp

@if($as === 'a' && $href)
    <a href="{{ $href }}"
       {{ $restAttributes }}
       role="menuitem"
       x-on:click="{{ $finalClick }}">
        @if($icon)<x-icon :name="$icon" class="w-4 h-4 flex-shrink-0" />@endif
        <span class="flex-1">{{ $slot }}</span>
        @if($badge)
            <span class="ml-auto px-1.5 py-0.5 text-xs bg-primary-100 text-primary-700 rounded-full">
                {{ $badge }}
            </span>
        @endif
    </a>
@else
    <button type="button"
            {{ $restAttributes }}
            role="menuitem"
            x-on:click="{{ $finalClick }}"
            @if($disabled) disabled @endif>
        @if($icon)<x-icon :name="$icon" class="w-4 h-4 flex-shrink-0" />@endif
        <span class="flex-1">{{ $slot }}</span>
        @if($badge)
            <span class="ml-auto px-1.5 py-0.5 text-xs bg-primary-100 text-primary-700 rounded-full">
                {{ $badge }}
            </span>
        @endif
    </button>
@endif