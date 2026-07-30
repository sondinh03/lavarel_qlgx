{{-- Tab cách tính điểm: tỉ lệ các thành phần, quy đổi chuyên cần, thang xếp loại --}}
@if(!$selectedNamHoc)
<x-stats.page-empty
    :panel="false"
    tone="slate"
    title="Vui lòng chọn năm học"
    description="Cách tính điểm được lưu theo từng năm học để không làm thay đổi điểm các năm trước" />
@else
@php
    $componentSum = round((float) $weightAcademic + (float) $weightClassAttendance + (float) $weightMassAttendance, 2);
    $semesterSum  = round((float) $weightSemester1 + (float) $weightSemester2, 2);
    $componentOk  = abs($componentSum - 100) <= 0.01;
    $semesterOk   = abs($semesterSum - 100) <= 0.01;
    $numberClass  = 'w-full h-11 px-4 rounded-xl border border-black/[0.06] bg-white/80 text-sm font-semibold
                     text-slate-900 shadow-mac-sm text-right tabular-nums
                     focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40';
@endphp

<div class="p-4 lg:p-6 space-y-5 max-w-3xl mx-auto">
    @if($errors->any())
    <div class="bg-red-50/90 border border-red-200/80 rounded-xl p-4 shadow-mac-sm text-sm text-red-700 space-y-1">
        @foreach($errors->all() as $err)<div>• {{ $err }}</div>@endforeach
    </div>
    @endif

    {{-- Phạm vi áp dụng --}}
    <div class="rounded-xl border border-black/[0.06] bg-white/60 p-4 shadow-mac-sm space-y-3">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Phạm vi áp dụng</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <label class="flex flex-col gap-0.5 p-3 rounded-xl border cursor-pointer transition-all select-none shadow-mac-sm
                          {{ $weightScope === 'parish'
                              ? 'border-primary-300/60 bg-primary-50/80'
                              : 'border-black/[0.06] bg-white/80 hover:bg-white' }}">
                <input type="radio" wire:model="weightScope" value="parish" class="sr-only">
                <span class="text-sm font-semibold text-slate-800">Toàn giáo xứ</span>
                <span class="text-xs text-slate-400">
                    Mọi lớp trong năm {{ $availableNamHocs->firstWhere('id', $selectedNamHoc)?->name ?? '' }}
                </span>
            </label>

            <label class="flex flex-col gap-0.5 p-3 rounded-xl border cursor-pointer transition-all select-none shadow-mac-sm
                          {{ $weightScope === 'grade'
                              ? 'border-primary-300/60 bg-primary-50/80'
                              : 'border-black/[0.06] bg-white/80 hover:bg-white' }}">
                <input type="radio" wire:model="weightScope" value="grade" class="sr-only">
                <span class="text-sm font-semibold text-slate-800">Riêng một khối</span>
                <span class="text-xs text-slate-400">Ghi đè cấu hình toàn xứ cho khối đó</span>
            </label>
        </div>

        @if($weightScope === 'grade')
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">Chọn khối</label>
            <select wire:model="weightScopeGradeId"
                class="w-full h-11 px-4 rounded-xl border border-black/[0.06] bg-white/80 text-sm text-slate-900 shadow-mac-sm
                       focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40">
                <option value="">-- Chọn khối --</option>
                @foreach($availableGrades as $grade)
                <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                @endforeach
            </select>
            @error('weightScopeGradeId')<p class="mt-1 text-sm text-red-500">{{ $message }}</p>@enderror
        </div>
        @endif

        <p class="text-xs {{ $weightOverrideExists ? 'text-emerald-700' : 'text-amber-700' }}">
            @if($weightOverrideExists)
                Phạm vi này đang có cấu hình riêng.
            @else
                Phạm vi này chưa có cấu hình riêng — đang thừa hưởng: <strong>{{ $weightSourceLabel }}</strong>.
                Lưu lại để tạo cấu hình riêng.
            @endif
        </p>
    </div>

    {{-- Tỉ lệ trong học kỳ --}}
    <div class="rounded-xl border border-black/[0.06] bg-white/60 p-4 shadow-mac-sm space-y-3">
        <div class="flex items-center justify-between gap-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Tỉ lệ trong điểm trung bình học kỳ</p>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold
                {{ $componentOk ? 'bg-emerald-50/80 text-emerald-700' : 'bg-red-50/80 text-red-600' }}">
                Tổng {{ $this->weightLabel($componentSum) }}%
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Trung bình học tập (%)</label>
                <input type="number" step="1" min="0" max="100"
                    wire:model.lazy="weightAcademic" class="{{ $numberClass }}">
                <p class="mt-1 text-[11px] text-slate-400">Trung bình có hệ số của các cột điểm</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Chuyên cần học (%)</label>
                <input type="number" step="1" min="0" max="100"
                    wire:model.lazy="weightClassAttendance" class="{{ $numberClass }}">
                <p class="mt-1 text-[11px] text-slate-400">Từ điểm danh buổi học</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Chuyên cần lễ (%)</label>
                <input type="number" step="1" min="0" max="100"
                    wire:model.lazy="weightMassAttendance" class="{{ $numberClass }}">
                <p class="mt-1 text-[11px] text-slate-400">Từ điểm danh thánh lễ</p>
            </div>
        </div>

        @unless($componentOk)
        <p class="text-xs text-red-600">Ba tỉ lệ phải cộng lại đúng 100% mới lưu được.</p>
        @endunless
    </div>

    {{-- Tỉ lệ cả năm --}}
    <div class="rounded-xl border border-black/[0.06] bg-white/60 p-4 shadow-mac-sm space-y-3">
        <div class="flex items-center justify-between gap-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Tỉ lệ trong điểm trung bình cả năm</p>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold
                {{ $semesterOk ? 'bg-emerald-50/80 text-emerald-700' : 'bg-red-50/80 text-red-600' }}">
                Tổng {{ $this->weightLabel($semesterSum) }}%
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Học kỳ 1 (%)</label>
                <input type="number" step="1" min="0" max="100"
                    wire:model.lazy="weightSemester1" class="{{ $numberClass }}">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Học kỳ 2 (%)</label>
                <input type="number" step="1" min="0" max="100"
                    wire:model.lazy="weightSemester2" class="{{ $numberClass }}">
            </div>
        </div>

        @unless($semesterOk)
        <p class="text-xs text-red-600">Hai tỉ lệ phải cộng lại đúng 100% mới lưu được.</p>
        @endunless

        <p class="text-[11px] text-slate-400">
            Điểm cả năm chỉ có khi cả hai học kỳ đều đã có điểm trung bình.
        </p>
    </div>

    {{-- Quy đổi chuyên cần --}}
    <div class="rounded-xl border border-black/[0.06] bg-white/60 p-4 shadow-mac-sm space-y-3">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Cách quy đổi chuyên cần</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Vắng có phép tính bằng (%)</label>
                <input type="number" step="5" min="0" max="100"
                    wire:model.lazy="excusedCreditPercent" class="{{ $numberClass }}">
                <p class="mt-1 text-[11px] text-slate-400">
                    So với một buổi có mặt. Ví dụ 50% = nửa buổi.
                </p>
            </div>

            <div class="text-xs text-slate-500 leading-relaxed rounded-xl bg-slate-50/80 p-3">
                <p class="font-semibold text-slate-600 mb-1">Công thức</p>
                Điểm chuyên cần = 10 × (số buổi có mặt + tỉ lệ trên × số buổi vắng có phép) / số buổi đã điểm danh.
                Buổi bị hủy và buổi chưa ai điểm danh không vào mẫu số.
                Ô trống sau giờ chốt / khi khóa được tính là KP.
                <a href="{{ route('help.scores') }}" class="font-semibold text-primary-600 hover:text-primary-700">Xem hướng dẫn →</a>
            </div>
        </div>
    </div>

    {{-- Xếp loại học lực --}}
    <div class="rounded-xl border border-black/[0.06] bg-white/60 p-4 shadow-mac-sm space-y-3">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Cách xếp loại học lực</p>

        <p class="text-xs text-slate-500 leading-relaxed">
            Xếp loại lấy theo điểm trung bình học kỳ (hoặc trung bình cả năm ở bảng tổng kết).
            Thang này cố định cho toàn hệ thống, không thay đổi theo tỉ lệ ở trên.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
            @foreach($this->ratingScale() as $level)
            <div class="flex items-center justify-between gap-2 p-2.5 rounded-xl bg-white/80 border border-black/[0.06]">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $level['badge'] }}">
                    {{ $level['label'] }}
                </span>
                <span class="text-xs font-semibold text-slate-600 tabular-nums">{{ $level['range'] }}</span>
            </div>
            @endforeach
        </div>

        <p class="text-[11px] text-slate-400">
            Học sinh chưa đủ dữ liệu để tính trung bình thì không được xếp loại.
        </p>
    </div>

    {{-- Xem trước --}}
    @if($gradingPreview)
    <div class="rounded-xl border border-primary-200/70 bg-primary-50/50 p-4 shadow-mac-sm space-y-2">
        <p class="text-xs font-semibold text-primary-700 uppercase tracking-wide">
            Xem trước với tỉ lệ đang nhập · học kỳ {{ $selectedSemester }}
        </p>
        <p class="text-sm text-slate-700">
            Học sinh <strong>{{ $gradingPreview['student_name'] }}</strong>
        </p>
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="px-2 py-1 rounded-lg bg-white/80 text-slate-600">
                TB học tập: <strong>{{ $gradingPreview['breakdown']['academic'] !== null ? number_format($gradingPreview['breakdown']['academic'], 1) : '—' }}</strong>
            </span>
            <span class="px-2 py-1 rounded-lg bg-white/80 text-slate-600">
                CC học: <strong>{{ $gradingPreview['breakdown']['class_attendance'] !== null ? number_format($gradingPreview['breakdown']['class_attendance'], 1) : '—' }}</strong>
            </span>
            <span class="px-2 py-1 rounded-lg bg-white/80 text-slate-600">
                CC lễ: <strong>{{ $gradingPreview['breakdown']['mass_attendance'] !== null ? number_format($gradingPreview['breakdown']['mass_attendance'], 1) : '—' }}</strong>
            </span>
            <span class="px-2 py-1 rounded-lg bg-primary-600 text-white font-semibold">
                TB kỳ: {{ $gradingPreview['breakdown']['total'] !== null ? number_format($gradingPreview['breakdown']['total'], 1) : '—' }}
            </span>
        </div>
        @if($gradingPreview['missing'])
        <p class="text-xs text-amber-700">{{ $gradingPreview['missing'] }} — nên chưa tính được TB cho học sinh này.</p>
        @endif
    </div>
    @elseif($selectedLop)
    <x-inline-tip>
        Lớp đang chọn chưa có dữ liệu điểm hoặc điểm danh nào để xem trước.
    </x-inline-tip>
    @else
    <x-inline-tip>
        Chọn một lớp ở bộ lọc phía trên để xem trước cách tính trên học sinh thật.
    </x-inline-tip>
    @endif

    <div class="flex flex-wrap gap-3">
        <x-button type="button" variant="primary" wire:click="saveGradingSettings" wire:loading.attr="disabled">
            <x-icon name="save" />
            Lưu cách tính
        </x-button>

        @if($weightOverrideExists)
        <x-button
            variant="danger"
            wire="deleteGradingSettings"
            confirm="Xoá cấu hình riêng của phạm vi này? Các lớp sẽ quay về dùng cấu hình rộng hơn.">
            Xoá cấu hình riêng
        </x-button>
        @endif
    </div>

    <p class="text-xs text-slate-400 leading-relaxed">
        Cấu hình chỉ áp dụng cho năm học đang chọn, nên điểm của các năm trước không thay đổi.
        Sau khi lưu, bảng điểm, thống kê và file Excel đều dùng tỉ lệ mới.
    </p>
</div>
@endif
