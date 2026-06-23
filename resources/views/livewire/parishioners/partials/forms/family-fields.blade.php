@php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="space-y-4">
    <p class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
        Hộ gia đình, vai trò (chồng/vợ/con) và liên kết cha/mẹ trong hệ thống được quản lý tại
        <a href="{{ route('families.index') }}" class="font-semibold text-primary-600 hover:text-primary-700">menu Gia đình</a>.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tên cha (văn bản)</label>
            <input wire:model.defer="father_name" type="text" class="{{ $input }}"
                placeholder="Khi chưa có hồ sơ cha trong hệ thống" />
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tên mẹ (văn bản)</label>
            <input wire:model.defer="mother_name" type="text" class="{{ $input }}"
                placeholder="Khi chưa có hồ sơ mẹ trong hệ thống" />
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Tình trạng hôn nhân (dân sự)</label>
            <select wire:model.defer="married" class="{{ $input }}">
                @foreach(config('parishioner.married', []) as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
