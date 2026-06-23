@php



$role = $role ?? 'groom';



$label = $role === 'groom' ? 'Bên nam' : 'Bên nữ';



$pidField = $role . '_parishioner_id';

$modeField = $role . '_parishioner_mode';

$manualNameField = $role . '_manual_name';

$impedimentField = $role . '_has_impediment';



$options = $role === 'groom' ? $maleParishionerOptions : $femaleParishionerOptions;

$selectedId = $role === 'groom' ? $groom_parishioner_id : $bride_parishioner_id;

$mode = $role === 'groom' ? $groom_parishioner_mode : $bride_parishioner_mode;

$manualName = $role === 'groom' ? $groom_manual_name : $bride_manual_name;



$input = 'w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500';



@endphp



<div class="space-y-4">



    <div>

        <label class="block text-sm font-medium text-slate-700 mb-2">{{ $label }}</label>



        <div class="inline-flex rounded-xl bg-slate-100 p-1 text-sm font-medium mb-3">

            <label class="cursor-pointer">

                <input type="radio" class="sr-only" wire:model="{{ $modeField }}" value="pick" />

                <span class="block px-3 py-1.5 rounded-lg transition {{ $mode === 'pick' ? 'bg-white shadow-sm text-primary-600 font-semibold' : 'text-slate-600' }}">

                    Chọn từ danh sách

                </span>

            </label>

            <label class="cursor-pointer">

                <input type="radio" class="sr-only" wire:model="{{ $modeField }}" value="manual" />

                <span class="block px-3 py-1.5 rounded-lg transition {{ $mode === 'manual' ? 'bg-white shadow-sm text-primary-600 font-semibold' : 'text-slate-600' }}">

                    Nhập tên thủ công

                </span>

            </label>

        </div>



        @if($mode === 'pick')

        <x-searchable-select

            wire:key="{{ $role }}-parishioner-{{ $pid ?? 'none' }}"

            wireModel="{{ $pidField }}"

            :options="$options"

            :live="true"

            placeholder="-- Tìm {{ strtolower($label) }} --"

            labelKey="name"

            valueKey="id"

            :value="$selectedId" />

        @error($pidField) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

        @if(count($options) === 0)

        <p class="text-xs text-amber-600 mt-1">Không tìm thấy giáo dân trong giáo xứ. Bạn có thể chọn "Nhập tên thủ công".</p>

        @endif

        @else

        <input

            wire:model.defer="{{ $manualNameField }}"

            type="text"

            class="{{ $input }}"

            placeholder="Nhập họ tên {{ strtolower($label) }}" />

        @error($manualNameField) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

        @endif

    </div>



    <label class="inline-flex items-center gap-2 text-sm text-slate-700">

        <input type="checkbox" wire:model.defer="{{ $impedimentField }}" class="rounded border-slate-300 text-primary-600" />

        Có ngăn trở hôn nhân

    </label>



    @include('livewire.marriage-announcement.partials.form-participant-parish-cascade', [

        'role' => $role,

        'prefix' => 'old',

        'prefixLabel' => 'Giáo xứ gốc (quê quán)',

    ])



    @include('livewire.marriage-announcement.partials.form-participant-parish-cascade', [

        'role' => $role,

        'prefix' => 'current',

        'prefixLabel' => 'Giáo xứ hiện tại',

    ])



    @include('livewire.marriage-announcement.partials.form-participant-parish-cascade', [

        'role' => $role,

        'prefix' => 'before',

        'prefixLabel' => 'Giáo xứ trước đó',

        'optional' => true,

    ])



</div>


