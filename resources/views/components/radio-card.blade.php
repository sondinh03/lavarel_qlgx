@props([
    'value',
    'label',
    'checked' => false,
])

@php
$model = $attributes->wire('model')->value();
@endphp

<button type="button"
    role="radio"
    value="{{ $value }}"
    aria-checked="{{ $checked ? 'true' : 'false' }}"
    wire:click="$set('{{ $model }}', '{{ $value }}')"
    class="w-full px-4 py-3 rounded-xl border text-left transition-all duration-200
        hover:bg-slate-50 hover:shadow-sm
        {{ $checked ? 'border-primary-500 bg-primary-50 shadow-sm' : 'border-slate-200' }}">

    <div class="flex items-center gap-2">
        <div class="w-4 h-4 rounded-full border flex items-center justify-center
            {{ $checked ? 'border-primary-500' : 'border-slate-300' }}">
            @if($checked)
                <div class="w-2 h-2 rounded-full bg-primary-500"></div>
            @endif
        </div>

        <span class="font-semibold
            {{ $checked ? 'text-primary-700' : 'text-slate-700' }}">
            {{ $label }}
        </span>
    </div>
</button>