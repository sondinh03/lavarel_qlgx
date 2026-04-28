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
    {{-- Label --}}
    @if($label)
    <label class="block text-sm font-semibold text-slate-700 mb-2">
        {{ $label }}
        @if($required)
        <span class="text-red-500">*</span>
        @endif
    </label>
    @endif

    {{-- Select Container --}}
    <div class="relative">
        <select
            {{ $wireModel ? "wire:model.defer={$wireModel}" : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge([
                'class' => 'w-full px-4 py-2 bg-white border rounded-xl text-slate-900 
                           focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
                           transition-all cursor-pointer appearance-none ' .
                           ($error ? 'border-red-300' : 'border-slate-200') .
                           ($disabled ? ' opacity-50 cursor-not-allowed bg-slate-50' : '')
            ]) }}>

            {{-- Placeholder Option --}}
            @if($placeholder)
            <option value="">{{ $placeholder }}</option>
            @endif

            {{-- Options Loop --}}
            @foreach($options as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
            @endforeach
        </select>

        {{-- Custom Dropdown Icon --}}
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg class="w-5 h-5 text-slate-400 transition-colors"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>

    {{-- Error Message --}}
    @if($error)
    <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>