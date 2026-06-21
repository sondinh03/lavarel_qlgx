@php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @if(!empty($parishionerSearchOptions))
    <div class="md:col-span-2">
        <x-searchable-select wire:model="spouse_id" label="Vợ/Chồng (trong hệ thống)" placeholder="Tìm giáo dân..."
            :options="$parishionerSearchOptions" option-value="id" option-label="name" />
    </div>
    @endif
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Ngày hôn phối</label>
        <input wire:model.defer="married_date" type="date" class="{{ $input }}" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Số giấy chứng nhận</label>
        <input wire:model.defer="certificate_number" type="text" class="{{ $input }}" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Giáo xứ hôn phối</label>
        <input wire:model.defer="marriage_parish_name" type="text" class="{{ $input }}" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Tỉnh/TP nơi cử hành</label>
        <input wire:model.defer="place_province" type="text" class="{{ $input }}" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Linh mục chứng kiến</label>
        <input wire:model.defer="priest_witness" type="text" class="{{ $input }}" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái hôn nhân</label>
        <select wire:model.defer="marriage_status" class="{{ $input }}">
            <option value="valid">Hợp lệ</option>
            <option value="invalid">Không hợp lệ</option>
            <option value="widowed">Góa</option>
            <option value="divorced">Ly hôn</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Người làm chứng 1</label>
        <input wire:model.defer="witness_1" type="text" class="{{ $input }}" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Người làm chứng 2</label>
        <input wire:model.defer="witness_2" type="text" class="{{ $input }}" />
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú</label>
        <textarea wire:model.defer="marriage_note" rows="2" class="{{ $input }}"></textarea>
    </div>
</div>
