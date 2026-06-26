@php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="space-y-4">
    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Phân cấp giáo hội</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Giáo phận</label>
            <x-searchable-select
                wireModel="diocese_id"
                :options="$dioceses"
                :live="true"
                placeholder="-- Chọn giáo phận --"
                labelKey="name"
                valueKey="id"
                :value="$diocese_id" />
            @error('diocese_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Giáo hạt</label>
            <x-searchable-select
                wire:key="deanery-{{ $diocese_id ?? 'none' }}"
                wireModel="deanery_id"
                :options="$deaneryOptions"
                :live="true"
                placeholder="{{ $diocese_id ? '-- Chọn giáo hạt --' : 'Chọn giáo phận trước' }}"
                labelKey="name"
                valueKey="id"
                :value="$deanery_id" />
            @error('deanery_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Giáo xứ</label>
            <x-searchable-select
                wire:key="parish-{{ $deanery_id ?? 'none' }}"
                wireModel="parish_id"
                :options="$parishOptions"
                :live="true"
                placeholder="{{ $deanery_id ? '-- Chọn giáo xứ --' : 'Chọn giáo hạt trước' }}"
                labelKey="name"
                valueKey="id"
                :value="$parish_id" />
            @error('parish_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Giáo họ</label>
            <x-searchable-select
                wire:key="parish-group-{{ $parish_id ?? 'none' }}"
                wireModel="parish_area_id"
                :options="$parishGroups"
                placeholder="{{ $parish_id ? '-- Chọn giáo họ --' : 'Chọn giáo xứ trước' }}"
                labelKey="name"
                valueKey="id"
                :value="$parish_area_id" />
            @error('parish_area_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Hội đoàn</label>
            <x-searchable-select
                wire:key="association-{{ $parish_id ?? 'none' }}"
                wireModel="association_id"
                :options="$associationOptions"
                placeholder="{{ $parish_id ? '-- Chọn hội đoàn --' : 'Chọn giáo xứ trước' }}"
                labelKey="name"
                valueKey="id"
                :value="$association_id" />
            @error('association_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide pt-2">Sinh hoạt & chuyển xứ</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Cấp bậc</label>
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
</div>
