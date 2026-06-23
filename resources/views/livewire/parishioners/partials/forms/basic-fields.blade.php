@php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Họ <span class="text-red-500">*</span></label>
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
        <x-searchable-select
            wireModel="saint_id"
            :options="$saints"
            placeholder="-- Chọn --"
            labelKey="name"
            valueKey="id"
            :value="$saint_id" />
        @error('saint_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
        <input wire:model.defer="birthday" type="date" class="{{ $input }} @error('birthday') border-red-400 @enderror" />
        @error('birthday') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Nơi sinh</label>
        <input wire:model.defer="birth_place" type="text" class="{{ $input }}" />
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Con thứ</label>
        <input wire:model.defer="birth_order" type="number" min="1" class="{{ $input }}" />
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">CCCD</label>
        <input wire:model.defer="cccd" type="text" maxlength="12" class="{{ $input }}" />
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Điện thoại</label>
        <input wire:model.defer="phone" type="tel" class="{{ $input }}" />
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
        <input wire:model.defer="email" type="email" class="{{ $input }} @error('email') border-red-400 @enderror" />
        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
</div>
