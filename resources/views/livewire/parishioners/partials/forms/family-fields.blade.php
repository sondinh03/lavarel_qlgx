@php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Tên cha (văn bản)</label>
        <input wire:model.defer="father_name" type="text" class="{{ $input }}" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Tên mẹ (văn bản)</label>
        <input wire:model.defer="mother_name" type="text" class="{{ $input }}" />
    </div>
    @if(!empty($parishionerSearchOptions))
    <div>
        <x-searchable-select wire:model="father_id" label="Cha (trong hệ thống)" placeholder="Tìm giáo dân..."
            :options="$parishionerSearchOptions" option-value="id" option-label="name" />
    </div>
    <div>
        <x-searchable-select wire:model="mother_id" label="Mẹ (trong hệ thống)" placeholder="Tìm giáo dân..."
            :options="$parishionerSearchOptions" option-value="id" option-label="name" />
    </div>
    @endif
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Vai trò trong gia đình</label>
        <select wire:model.defer="family_role" class="{{ $input }}">
            <option value="">-- Chọn --</option>
            <option value="husband">Chồng</option>
            <option value="wife">Vợ</option>
            <option value="child">Con</option>
            <option value="other">Khác</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Tình trạng hôn nhân</label>
        <select wire:model.defer="married" class="{{ $input }}">
            @foreach(config('parishioner.married', []) as $k => $v)
            <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">ID hộ gia đình</label>
        <input wire:model.defer="family_id" type="number" placeholder="ID hộ gia đình" class="{{ $input }}" />
    </div>
</div>
