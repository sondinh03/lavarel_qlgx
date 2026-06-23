@php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-slate-700 mb-1">Tên đôi hôn phối</label>
        <input wire:model.defer="name" type="text" class="{{ $input }}" placeholder="Nguyễn Văn A & Trần Thị B" />
        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Linh mục phụ trách</label>
        <x-searchable-select
            wireModel="priest_id"
            :options="$priests"
            placeholder="-- Chọn linh mục --"
            labelKey="name"
            valueKey="id"
            :value="$priest_id" />
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái</label>
        @if($groom_has_impediment || $bride_has_impediment)
        <p class="text-sm font-medium text-red-700 bg-red-50 border border-red-100 rounded-xl px-3 py-2">Có ngăn trở (tự động)</p>
        @elseif($announcements_one_done && $announcements_two_done && $announcements_three_done)
        <p class="text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-xl px-3 py-2">Hoàn thành (đủ 3 đợt rao)</p>
        @elseif((int) $status === 3)
        <select wire:model.defer="status" class="{{ $input }}">
            <option value="0">Đang rao (mở lại)</option>
            <option value="3">Đã hủy</option>
        </select>
        @else
        <select wire:model.defer="status" class="{{ $input }}">
            <option value="0">Đang rao</option>
            <option value="3">Đã hủy</option>
        </select>
        <p class="text-xs text-slate-400 mt-1">Chọn "Đã hủy" nếu hủy hồ sơ. Hoàn thành tự động khi đánh dấu đủ 3 đợt rao ở tab Lịch rao.</p>
        @endif
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Giáo phận</label>
        <x-searchable-select
            wireModel="did"
            :options="$dioceses"
            :live="true"
            placeholder="-- Chọn giáo phận --"
            labelKey="name"
            valueKey="id"
            :value="$did" />
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700 mb-1">Giáo hạt</label>
        <x-searchable-select
            wire:key="header-deanery-{{ $did ?? 'none' }}"
            wireModel="deid"
            :options="$deaneryOptions"
            :live="true"
            placeholder="{{ $did ? '-- Chọn giáo hạt --' : 'Chọn giáo phận trước' }}"
            labelKey="name"
            valueKey="id"
            :value="$deid" />
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-semibold text-slate-700 mb-1">Giáo xứ</label>
        <x-searchable-select
            wire:key="header-parish-{{ $deid ?? 'none' }}"
            wireModel="pid"
            :options="$parishOptions"
            :live="true"
            placeholder="{{ $deid ? '-- Chọn giáo xứ --' : 'Chọn giáo hạt trước' }}"
            labelKey="name"
            valueKey="id"
            :value="$pid" />
        @error('pid') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
</div>
