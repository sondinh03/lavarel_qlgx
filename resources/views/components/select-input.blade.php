@props([
    'label' => '',
    'placeholder' => '-- Chọn --',
    'options' => [],
    'wireModel' => '',
    'required' => false,
    'disabled' => false,
    'error' => null
])

<div class="w-full">
    @if($label)
        <label class="block text-sm font-semibold text-slate-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <select 
            {{ $wireModel ? "wire:model.live={$wireModel}" : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge([
                'class' => 'w-full px-4 py-2.5 bg-white border rounded-xl text-slate-900 focus:outline-none focus:ring-2 transition-all ' .
                           ($error ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-slate-200 focus:ring-blue-500 focus:border-transparent') .
                           ($disabled ? ' opacity-50 cursor-not-allowed bg-slate-50' : ' cursor-pointer')
            ]) }}>
            
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif

            @foreach($options as $value => $text)
                <option value="{{ $value }}">{{ $text }}</option>
            @endforeach
        </select>

        {{-- Dropdown Icon --}}
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>

    @if($error)
        <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>