@php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Quê quán</label>
        <input wire:model.defer="origin" type="text" class="{{ $input }}" />
    </div>
    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Thường trú</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tỉnh/TP</label>
            <input wire:model.defer="permanent_province" type="text" class="{{ $input }}" />
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Địa chỉ chi tiết</label>
            <input wire:model.defer="permanent_residence" type="text" class="{{ $input }}" />
        </div>
    </div>
    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide pt-2">Tạm trú</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tỉnh/TP</label>
            <input wire:model.defer="temporary_province" type="text" class="{{ $input }}" />
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Địa chỉ chi tiết</label>
            <input wire:model.defer="temporary_residence" type="text" class="{{ $input }}" />
        </div>
    </div>
</div>
