@php $input = "w-full px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="space-y-4">
    <div class="grid grid-cols-1 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Họ và tên đệm <span class="text-red-500">*</span></label>
            <input wire:model.defer="last_name" type="text" class="{{ $input }} @error('last_name') border-red-400 @enderror" />
            @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Tên <span class="text-red-500">*</span></label>
            <input wire:model.defer="first_name" type="text" class="{{ $input }} @error('first_name') border-red-400 @enderror" />
            @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Tên thánh</label>
            @if(!empty($saints))
            <x-searchable-select
                wireModel="saint_id"
                :options="$saints"
                placeholder="-- Chọn tên thánh --"
                labelKey="name"
                valueKey="id"
                :value="$saint_id" />
            <p class="text-xs text-slate-500 mt-1">Không có trong danh sách?</p>
            <input wire:model.defer="saint_name" type="text" class="{{ $input }} mt-1" placeholder="Nhập tên thánh khác" />
            @else
            <input wire:model.defer="saint_name" type="text" class="{{ $input }}" placeholder="VD: Giuse, Maria..." />
            @endif
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Giới tính <span class="text-red-500">*</span></label>
            <select wire:model.defer="gender" class="{{ $input }}">
                <option value="male">Nam</option>
                <option value="female">Nữ</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Ngày sinh</label>
            <input wire:model.defer="birthday" type="date" class="{{ $input }}" />
            @error('birthday') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Nơi sinh</label>
            <input wire:model.defer="birth_place" type="text" class="{{ $input }}" />
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Con thứ mấy</label>
            <input wire:model.defer="birth_order" type="number" min="1" class="{{ $input }}" />
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Số điện thoại <span class="text-red-500">*</span></label>
            <input wire:model.defer="phone" type="tel" inputmode="tel" class="{{ $input }} @error('phone') border-red-400 @enderror" />
            @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
            <input wire:model.defer="email" type="email" inputmode="email" class="{{ $input }}" />
            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">CCCD/CMND</label>
            <input wire:model.defer="cccd" type="text" inputmode="numeric" class="{{ $input }}" />
        </div>
        @if(!empty($parishGroups))
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Giáo họ</label>
            <x-searchable-select
                wireModel="parish_area_id"
                :options="$parishGroups"
                placeholder="-- Chọn giáo họ --"
                labelKey="name"
                valueKey="id"
                :value="$parish_area_id" />
        </div>
        @endif
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Ảnh đại diện</label>
            <input wire:model="avatar" type="file" accept="image/*" capture="user"
                class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-primary-50 file:text-primary-700" />
            @error('avatar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            <div wire:loading wire:target="avatar" class="text-xs text-slate-500 mt-1">Đang tải ảnh...</div>
        </div>
    </div>
</div>
