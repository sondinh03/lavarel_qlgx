<div>
    <label class="block text-sm font-medium text-slate-700 mb-1">
        {{ $label }}
        @if($required ?? false) <span class="text-red-500">*</span> @endif
    </label>
    <input wire:model.defer="{{ $wire }}"
           type="{{ $type ?? 'text' }}"
           class="w-full px-3 py-2 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-primary-500
                  @error($wire) border-red-400 @else border-slate-300 @enderror" />
    @error($wire)
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>