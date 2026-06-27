@php $input = "w-full px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="space-y-4">
    @if(count($parishOptions) > 1)
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Giáo xứ <span class="text-red-500">*</span></label>
        <x-searchable-select
            wireModel="targetParishId"
            :options="$parishOptions"
            placeholder="-- Chọn giáo xứ --"
            labelKey="name"
            valueKey="id"
            :live="true"
            :value="$targetParishId" />
        @error('targetParishId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    @elseif($parishDisplayLabel)
    <div class="text-sm bg-primary-50 border border-primary-100 rounded-xl px-4 py-3">
        <span class="text-slate-500">Giáo xứ:</span>
        <span class="font-semibold text-primary-700">{{ $parishDisplayLabel }}</span>
    </div>
    @endif

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Mã gia đình</label>
        <div class="px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-mono font-semibold text-primary-700">
            {{ $family_code ?? '—' }}
        </div>
        <p class="text-xs text-slate-500 mt-1">Mã này sẽ dùng cho hộ gia đình sau khi được duyệt.</p>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Tên hộ gia đình <span class="text-red-500">*</span></label>
        <input wire:model.defer="family_name" type="text" class="{{ $input }}" placeholder="VD: GĐ Nguyễn Văn An" />
        @error('family_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    @if(!empty($parishGroups))
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Giáo họ</label>
        <x-searchable-select
            wireModel="family_parish_area_id"
            :options="$parishGroups"
            placeholder="-- Chọn giáo họ --"
            labelKey="name"
            valueKey="id"
            :value="$family_parish_area_id" />
    </div>
    @endif

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Địa chỉ hộ gia đình</label>
        <input wire:model.defer="family_address" type="text" class="{{ $input }}" placeholder="Số nhà, đường..." />
    </div>

    <div class="grid grid-cols-1 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Tỉnh/TP</label>
            <x-searchable-select
                wireModel="family_province"
                :options="$provinces"
                :live="true"
                placeholder="-- Chọn tỉnh/TP --"
                labelKey="name"
                valueKey="id"
                :value="$family_province" />
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Xã/Phường</label>
            <x-searchable-select
                wire:key="family-ward-{{ $family_province ?? 'none' }}"
                wireModel="family_ward_id"
                :options="$familyWardOptions"
                :live="true"
                placeholder="{{ $family_province ? '-- Chọn xã/phường --' : 'Chọn tỉnh trước' }}"
                labelKey="name"
                valueKey="id"
                :value="$family_ward_id" />
        </div>
    </div>

    <p class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
        Bước tiếp theo: thêm từng thành viên trong hộ (cha, mẹ, con...) như trong sổ gia đình.
    </p>
</div>
