<label class="flex items-center gap-2 cursor-pointer select-none">
    <input wire:model.defer="{{ $wire }}" type="checkbox"
           class="w-4 h-4 rounded text-primary-600 border-slate-300 focus:ring-primary-500" />
    <span class="text-sm text-slate-700">{{ $label }}</span>
</label>