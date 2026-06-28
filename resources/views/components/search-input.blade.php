@props([
    'placeholder' => 'Tìm kiếm...',
    'wireModel'   => 'search',
    'debounce'    => '500ms'
])

<div {{ $attributes->merge(['class' => 'relative w-full']) }}
     x-data="{ hasValue: false }"
     x-init="
         hasValue = ($wire.{{ $wireModel }} ?? '').length > 0;
         $watch('$wire.{{ $wireModel }}', val => {
             hasValue = val !== null && val !== undefined && val.length > 0;
         });
     ">

    {{-- Search Icon --}}
    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 pointer-events-none"
         fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
    </svg>

    {{-- Input --}}
    <input
        type="text"
        wire:model.debounce.{{ $debounce }}="{{ $wireModel }}"
        placeholder="{{ $placeholder }}"
        x-on:input="hasValue = $event.target.value.length > 0"
        class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl
               text-sm text-slate-900 placeholder-slate-500 focus:outline-none
               focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">

    {{-- Clear Button --}}
    <button
        type="button"
        wire:click="$set('{{ $wireModel }}', '')"
        wire:loading.remove
        wire:target="{{ $wireModel }}"
        x-on:click="hasValue = false"
        x-show="hasValue"
        x-cloak
        x-transition
        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400
               hover:text-slate-600 transition-colors z-10"
        aria-label="Xóa tìm kiếm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- Spinner --}}
    <div wire:loading wire:target="{{ $wireModel }}"
         class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
        <svg class="animate-spin h-4 w-4 text-primary-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </div>
</div>
