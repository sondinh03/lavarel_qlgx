@php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Quê quán</label>
        <input wire:model.defer="origin" type="text" class="{{ $input }}" />
    </div>

    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Thường trú</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Tỉnh/TP</label>
            <x-searchable-select
                wireModel="permanent_province"
                :options="$provinces"
                :live="true"
                placeholder="-- Chọn tỉnh/TP --"
                labelKey="name"
                valueKey="id"
                :value="$permanent_province" />
            @error('permanent_province') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Xã/Phường</label>
            <x-searchable-select
                wire:key="permanent-ward-{{ $permanent_province ?? 'none' }}"
                wireModel="permanent_ward_id"
                :options="$permanentWardOptions"
                :live="true"
                placeholder="{{ $permanent_province ? '-- Chọn xã/phường --' : 'Chọn tỉnh trước' }}"
                labelKey="name"
                valueKey="id"
                :value="$permanent_ward_id" />
            @error('permanent_ward_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-slate-700 mb-1">Địa chỉ chi tiết</label>
            <input wire:model.defer="permanent_residence" type="text" class="{{ $input }}" />
        </div>
    </div>

    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide pt-2">Tạm trú</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Tỉnh/TP</label>
            <x-searchable-select
                wireModel="temporary_province"
                :options="$provinces"
                :live="true"
                placeholder="-- Chọn tỉnh/TP --"
                labelKey="name"
                valueKey="id"
                :value="$temporary_province" />
            @error('temporary_province') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Xã/Phường</label>
            <x-searchable-select
                wire:key="temporary-ward-{{ $temporary_province ?? 'none' }}"
                wireModel="temporary_ward_id"
                :options="$temporaryWardOptions"
                :live="true"
                placeholder="{{ $temporary_province ? '-- Chọn xã/phường --' : 'Chọn tỉnh trước' }}"
                labelKey="name"
                valueKey="id"
                :value="$temporary_ward_id" />
            @error('temporary_ward_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-slate-700 mb-1">Địa chỉ chi tiết</label>
            <input wire:model.defer="temporary_residence" type="text" class="{{ $input }}" />
        </div>
    </div>
</div>
