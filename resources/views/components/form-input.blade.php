@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'required' => false,
    'placeholder' => '',
    'hint' => '',
])

<div>
    @if($label)
    <label class="block text-sm font-semibold text-slate-700 mb-1">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge([
            'class' => 'w-full px-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500'
        ]) }}
        @if($attributes->has('wire:model')) wire:model.defer="{{ $attributes->get('wire:model') }}" @endif>

    @if($hint)
    <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif

    {{-- @error($name)
    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
    @enderror --}}
</div>