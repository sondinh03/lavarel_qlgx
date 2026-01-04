@props([
'label' => '',
'name' => '',
'type' => 'text',
'required' => false,
'placeholder' => '',
])

<div>
    <label class="block text-sm font-semibold text-slate-700 mb-1">
        {{ $label }}
        @if($required)
        <span class="text-red-500">*</span>
        @endif
    </label>

    <input
        type="{{ $type }}"
        wire:model.defer="{{ $name }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge([
            'class' => 'w-full px-3 py-2 rounded-xl border focus:outline-none focus:ring-2 ' . 
                       ($errors->has($name) 
                           ? 'border-red-300 focus:ring-red-500' 
                           : 'border-slate-300 focus:ring-primary-500')
        ]) }}>

    @error($name)
    <p class="mt-1 text-sm text-red-500 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01" />
        </svg>
        {{ $message }}
    </p>
    @enderror
</div>