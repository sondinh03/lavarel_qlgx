@php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="space-y-4">
    <label class="flex items-center gap-2">
        <input type="checkbox" wire:model="is_deceased" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
        <span class="text-sm font-medium text-slate-700">Đã qua đời</span>
    </label>
    @if($is_deceased)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Ngày mất <span class="text-red-500">*</span></label>
            <input wire:model.defer="death_date" type="date" class="{{ $input }} @error('death_date') border-red-400 @enderror" />
            @error('death_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Giờ từ trần</label>
            <input wire:model.defer="death_time" type="text" class="{{ $input }}" placeholder="VD: 14:30" />
            @error('death_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Số sổ mất</label>
            <input wire:model.defer="death_book_number" type="text" class="{{ $input }}" />
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nơi qua đời</label>
            <input wire:model.defer="death_place" type="text" class="{{ $input }}" />
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Nơi an táng</label>
            <input wire:model.defer="burial_place" type="text" class="{{ $input }}" />
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nghi thức tẩm liệm</label>
            <input wire:model.defer="embalm_at" type="datetime-local" class="{{ $input }}" />
            @error('embalm_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Thánh lễ đưa chân</label>
            <input wire:model.defer="farewell_mass_at" type="datetime-local" class="{{ $input }}" />
            @error('farewell_mass_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Thánh lễ an táng</label>
            <input wire:model.defer="burial_mass_at" type="datetime-local" class="{{ $input }}" />
            @error('burial_mass_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>
    @endif
</div>
