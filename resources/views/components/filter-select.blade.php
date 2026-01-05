@props([
    'label' => '',
    'model' => '',
    'options' => [],
    'placeholder' => '-- Chọn --',
    'disabled' => false,
])

<div {{ $attributes->merge(['class' => 'w-48']) }}> {{-- ✅ Width cố định 192px --}}
    @if($label)
    <label class="block text-sm font-semibold text-slate-700 mb-2">
        {{ $label }}
    </label>
    @endif
    
    <select 
        wire:model.live="{{ $model }}"
        @disabled($disabled)
        class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl 
               text-slate-900 text-sm
               focus:outline-none focus:ring-2 focus:ring-primary-500 
               disabled:bg-slate-50 disabled:text-slate-400 disabled:cursor-not-allowed
               transition-colors">
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>