<div>
    <label class="block text-sm font-semibold text-slate-700 mb-1">
        {{ $label }}
        @if($required ?? false) <span class="text-red-500">*</span> @endif
    </label>
    <select wire:model.defer="{{ $wire }}"
        class="w-full px-3 py-2 rounded-xl border bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500
               @error($wire) border-red-400 @else border-slate-300 @enderror">
        @if($nullable ?? false)
            <option value="">-- Chọn --</option>
        @endif
        @foreach($options as $val => $opt)
            <option value="{{ $val }}">{{ $opt }}</option>
        @endforeach
    </select>
    @error($wire)
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>