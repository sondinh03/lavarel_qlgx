@php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Giáo họ</label>
        <select wire:model.defer="parish_area_id" class="{{ $input }}">
            <option value="">-- Chọn giáo họ --</option>
            @foreach($parishGroups as $id => $name)
            <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Cấp bậc</label>
        <select wire:model.defer="level" class="{{ $input }}">
            <option value="">-- Chọn --</option>
            @foreach(config('parishioner.level', []) as $k => $v)
            <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Ngày gia nhập xứ</label>
        <input wire:model.defer="joined_date" type="date" class="{{ $input }}" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Ngày chuyển đến</label>
        <input wire:model.defer="transferred_date" type="date" class="{{ $input }}" />
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Lý do rời xứ</label>
        <input wire:model.defer="left_reason" type="text" class="{{ $input }}" />
    </div>
</div>
