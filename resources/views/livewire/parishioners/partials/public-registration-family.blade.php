@php $input = "w-full px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="space-y-4">
    <p class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
        Thông tin theo sổ gia đình công giáo: vai trò trong hộ, tên cha mẹ, chủ hộ.
    </p>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Vai trò trong gia đình</label>
        <select wire:model.defer="family_role" class="{{ $input }}">
            <option value="">-- Chọn --</option>
            @foreach($familyRoles as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('family_role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Tên chủ hộ (theo sổ gia đình)</label>
        <input wire:model.defer="family_head_name" type="text" class="{{ $input }}"
            placeholder="Họ tên người đứng tên hộ gia đình" />
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Tên cha</label>
        <input wire:model.defer="father_name" type="text" class="{{ $input }}" />
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Tên mẹ</label>
        <input wire:model.defer="mother_name" type="text" class="{{ $input }}" />
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Tên vợ/chồng (nếu có)</label>
        <input wire:model.defer="spouse_name" type="text" class="{{ $input }}" />
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Tình trạng hôn nhân</label>
        <select wire:model.defer="married" class="{{ $input }}">
            @foreach(config('parishioner.married', []) as $k => $v)
            <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Ghi chú thêm</label>
        <textarea wire:model.defer="note" rows="3" class="{{ $input }} resize-none"
            placeholder="Thông tin bổ sung từ sổ gia đình..."></textarea>
    </div>
</div>
