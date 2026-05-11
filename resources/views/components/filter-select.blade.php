@props([
    'label' => '',
    'options' => [],
    'placeholder' => '-- Chọn --',
    'disabled' => false,
])

<div x-data="{ open: false }"
    x-on:click.outside="open = false"
    {{ $attributes->only(['class'])->merge(['class' => 'relative w-48']) }}>

    @if($label)
        <label class="block text-sm font-semibold text-slate-700 mb-2">{{ $label }}</label>
    @endif

    {{-- Hidden select để wire:model hoạt động --}}
    <select
        {{ $attributes->except(['class', 'label', 'options', 'placeholder', 'disabled']) }}
        @disabled($disabled)
        x-ref="nativeSelect"
        x-on:change="open = false"
        class="sr-only">
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
        @endforeach
    </select>

    {{-- Trigger --}}
    <button type="button"
        x-on:click="open = !open"
        :class="open ? 'ring-2 ring-primary-500 border-transparent' : 'border-slate-200'"
        @disabled($disabled)
        class="w-full px-3 py-2 rounded-xl border bg-white text-sm text-left
               flex items-center justify-between gap-2 transition-all focus:outline-none
               disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed">
        <span class="truncate"
            :class="$refs.nativeSelect.value ? 'text-slate-900' : 'text-slate-400'"
            x-text="$refs.nativeSelect.selectedOptions[0]?.text || '{{ $placeholder }}'">
        </span>
        <svg class="w-4 h-4 text-slate-400 flex-shrink-0 transition-transform"
            :class="open ? 'rotate-180' : ''"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-cloak
        class="absolute z-50 w-full mt-1 bg-white rounded-xl border border-slate-200 shadow-lg overflow-hidden">
        <ul class="py-1">
            <li x-on:click="$refs.nativeSelect.value = ''; $refs.nativeSelect.dispatchEvent(new Event('change')); open = false"
                :class="!$refs.nativeSelect.value ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-400 hover:bg-slate-50'"
                class="px-3 py-2 text-sm cursor-pointer transition-colors">
                {{ $placeholder }}
            </li>
            @foreach($options as $value => $text)
            <li x-on:click="$refs.nativeSelect.value = '{{ $value }}'; $refs.nativeSelect.dispatchEvent(new Event('change')); open = false"
                :class="$refs.nativeSelect.value === '{{ $value }}' ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-700 hover:bg-slate-50'"
                class="px-3 py-2 text-sm cursor-pointer transition-colors flex items-center justify-between">
                <span>{{ $text }}</span>
                <svg x-show="$refs.nativeSelect.value === '{{ $value }}'"
                    class="w-4 h-4 text-primary-500 flex-shrink-0"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </li>
            @endforeach
        </ul>
    </div>
</div>