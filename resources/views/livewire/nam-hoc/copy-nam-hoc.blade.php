<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-3xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Quản lý năm học', 'url' => route('school-years.index')],
            ['label' => 'Copy cấu trúc lớp'],
        ]" separator="arrow" />

        {{-- Toast --}}
        <div role="status" aria-live="polite">
            @if (session()->has('message'))
            <x-toast-notification type="success" :duration="3500">{{ session('message') }}</x-toast-notification>
            @endif
            @if (session()->has('error'))
            <x-toast-notification type="error" :duration="4000">{{ session('error') }}</x-toast-notification>
            @endif
            @if (session()->has('warning'))
            <x-toast-notification type="warning" :duration="4000">{{ session('warning') }}</x-toast-notification>
            @endif
        </div>

        {{-- Step Indicator --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-6 py-4">
            <div class="flex items-center">
                @foreach([1 => 'Chọn năm', 2 => 'Xác nhận', 3 => 'Hoàn tất', 4 => 'Xếp học sinh'] as $s => $label)
                <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
                    <div class="flex items-center gap-1.5 shrink-0">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                            {{ $step > $s ? 'bg-emerald-500 text-white'
                                : ($step === $s ? 'bg-primary-600 text-white'
                                : 'bg-slate-200 text-slate-500') }}">
                            @if($step > $s)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            @else
                            {{ $s }}
                            @endif
                        </div>
                        <span class="text-xs font-medium hidden sm:block
                            {{ $step === $s ? 'text-primary-700' : 'text-slate-400' }}">
                            {{ $label }}
                        </span>
                    </div>
                    @if(!$loop->last)
                    <div class="flex-1 h-px bg-slate-200 mx-2"></div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             BƯỚC 1: Chọn năm
        ══════════════════════════════════════════ --}}
        @if($step === 1)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Copy cấu trúc lớp</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Tạo lại danh sách lớp cho năm mới. Xếp học sinh vào lớp ở bước sau.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Năm nguồn <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="sourceNamHocId"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-300
                               focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                        <option value="">-- Chọn năm nguồn --</option>
                        @foreach($namHocs as $nh)
                        <option value="{{ $nh->id }}">{{ $nh->name }}</option>
                        @endforeach
                    </select>
                    @error('sourceNamHocId')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Năm đích <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="targetNamHocId"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-300
                               focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                        <option value="">-- Chọn năm đích --</option>
                        @foreach($namHocs as $nh)
                        <option value="{{ $nh->id }}" @disabled($nh->id == $sourceNamHocId)>
                            {{ $nh->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('targetNamHocId')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Tuỳ chọn --}}
            <div class="border border-slate-200 rounded-xl p-4">
                <label class="flex items-start gap-3 cursor-pointer group">
                    <input type="checkbox" wire:model="copyScoreTypes"
                        class="mt-0.5 w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                    <div>
                        <div class="text-sm font-medium text-slate-900">Copy cấu hình loại điểm</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Copy ScoreType (khảo kinh, 15 phút, 45 phút, giữa kỳ, cuối kỳ) vào các lớp mới
                        </div>
                    </div>
                </label>
            </div>

            {{-- Preview lớp --}}
            @if($sourceNamHocId)
            @if($sourceClasses->isNotEmpty())
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        {{ $sourceClasses->count() }} lớp sẽ được copy
                    </span>
                    @if($copyScoreTypes)
                    <span class="text-xs text-indigo-600 font-medium">+ cấu hình điểm</span>
                    @endif
                </div>
                <div class="divide-y divide-slate-100 max-h-52 overflow-y-auto">
                    @foreach($sourceClasses as $class)
                    <div class="px-4 py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-slate-900">{{ $class->name }}</span>
                            <span class="text-xs text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">
                                {{ $class->gradeLevel?->name ?? 'N/A' }}
                            </span>
                        </div>
                        @if($copyScoreTypes && ($class->score_types_count ?? 0) > 0)
                        <span class="text-xs text-slate-400">{{ $class->score_types_count }} loại điểm</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="border border-amber-200 bg-amber-50 rounded-xl px-4 py-3 text-sm text-amber-700">
                Năm nguồn chưa có lớp nào
            </div>
            @endif
            @endif

            <div class="flex justify-end">
                <x-action-button wire="proceedToConfirm" icon="arrow-right"
                    :disabled="!$sourceNamHocId || !$targetNamHocId || $sourceClasses->isEmpty()">
                    Xem xác nhận
                </x-action-button>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════
             BƯỚC 2: Xác nhận
        ══════════════════════════════════════════ --}}
        @if($step === 2)
        @php
        $sourceYear = $namHocs->find($sourceNamHocId);
        $targetYear = $namHocs->find($targetNamHocId);
        $totalScoreTypes = $copyScoreTypes ? $sourceClasses->sum('score_types_count') : 0;
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-5">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Xác nhận copy</h2>
                <p class="text-sm text-slate-500 mt-1">Kiểm tra lại trước khi thực hiện</p>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 flex items-center justify-center gap-6">
                <div class="text-center">
                    <div class="text-base font-bold text-slate-900">{{ $sourceYear?->name }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">Năm nguồn</div>
                </div>
                <svg class="w-6 h-6 text-primary-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
                <div class="text-center">
                    <div class="text-base font-bold text-primary-600">{{ $targetYear?->name }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">Năm đích</div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="border border-slate-200 rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold text-primary-600">{{ $sourceClasses->count() }}</div>
                    <div class="text-sm text-slate-500 mt-1">Lớp sẽ được tạo</div>
                </div>
                <div class="border border-slate-200 rounded-xl p-4 text-center {{ $copyScoreTypes ? '' : 'opacity-40' }}">
                    <div class="text-3xl font-bold text-indigo-600">{{ $totalScoreTypes }}</div>
                    <div class="text-sm text-slate-500 mt-1">
                        Loại điểm
                        @if(!$copyScoreTypes)<span class="block text-xs text-amber-500">(không copy)</span>@endif
                    </div>
                </div>
            </div>

            <div class="bg-amber-50 border-l-4 border-amber-400 rounded-xl p-4 text-sm text-amber-700">
                Lớp trùng tên trong năm đích sẽ bị bỏ qua. Thao tác không thể hoàn tác tự động.
            </div>

            <div class="flex justify-between">
                <x-action-button wire="backToSelectYear" variant="secondary" icon="arrow-left">
                    Quay lại
                </x-action-button>
                <x-action-button wire="confirmCopy" icon="save" :loading="true" :disabled="$processing">
                    {{ $processing ? 'Đang xử lý...' : 'Xác nhận copy' }}
                </x-action-button>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════
             BƯỚC 3: Hoàn tất copy cấu trúc
        ══════════════════════════════════════════ --}}
        @if($step === 3)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Copy cấu trúc hoàn tất!</h2>
                    <p class="text-sm text-slate-500">
                        {{ $result['source_name'] }} → {{ $result['target_name'] }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="bg-slate-50 rounded-xl p-3 text-center">
                    <div class="text-2xl font-bold text-primary-600">{{ $result['created_classes'] ?? 0 }}</div>
                    <div class="text-xs text-slate-500 mt-1">Lớp đã tạo</div>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 text-center">
                    <div class="text-2xl font-bold text-indigo-600">{{ $result['copied_score_types'] ?? 0 }}</div>
                    <div class="text-xs text-slate-500 mt-1">Loại điểm</div>
                </div>
                <div class="bg-slate-50 rounded-xl p-3 text-center">
                    <div class="text-2xl font-bold text-amber-500">{{ $result['skipped_classes'] ?? 0 }}</div>
                    <div class="text-xs text-slate-500 mt-1">Lớp bỏ qua</div>
                </div>
            </div>

            {{-- CTA tiếp theo --}}
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-sm font-semibold text-blue-800">Bước tiếp theo: Xếp học sinh vào lớp</p>
                <p class="text-sm text-blue-600 mt-1">
                    Chọn lớp đích trong <span class="font-semibold">{{ $result['target_name'] }}</span>,
                    lấy học sinh từ lớp cũ và tick chọn để xếp vào lớp mới.
                </p>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('school-years.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-slate-300
                           text-slate-600 rounded-xl hover:bg-slate-50 transition text-sm font-semibold">
                    Bỏ qua, về năm học
                </a>
                <x-action-button wire="proceedToAssign" icon="arrow-right">
                    Xếp học sinh ngay
                </x-action-button>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════
             BƯỚC 4: Xếp học sinh
        ══════════════════════════════════════════ --}}
        @if($step === 4)
        <div class="space-y-4">

            {{-- Header --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Xếp học sinh</h2>
                        <p class="text-sm text-slate-500 mt-0.5">
                            Năm đích: <span class="font-semibold text-primary-600">{{ $result['target_name'] }}</span>
                        </p>
                    </div>
                    <button wire:click="startOver"
                        class="text-sm text-slate-400 hover:text-slate-600 transition">
                        Làm lại từ đầu
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                {{-- CỘT TRÁI: Chọn lớp đích + lớp nguồn --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-4">
                    <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Chọn lớp</h3>

                    {{-- Lớp đích --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Lớp đích <span class="text-red-500">*</span>
                            <span class="text-xs text-slate-400 font-normal ml-1">({{ $result['target_name'] }})</span>
                        </label>
                        <select wire:model="targetClassId"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                            <option value="">-- Chọn lớp đích --</option>
                            @foreach($targetClasses as $class)
                            <option value="{{ $class->id }}">
                                {{ $class->name }}
                                @if($class->gradeLevel) ({{ $class->gradeLevel->name }}) @endif
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Lớp nguồn --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Lấy học sinh từ lớp <span class="text-red-500">*</span>
                            <span class="text-xs text-slate-400 font-normal ml-1">({{ $result['source_name'] }})</span>
                        </label>
                        <select wire:model="sourceClassId"
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">
                            <option value="">-- Chọn lớp nguồn --</option>
                            @foreach($sourceClassList as $class)
                            <option value="{{ $class->id }}">
                                {{ $class->name }}
                                @if($class->gradeLevel) ({{ $class->gradeLevel->name }}) @endif
                            </option>
                            @endforeach
                        </select>
                        @if($sourceClassId && $availableStudents->isEmpty())
                        <p class="mt-1.5 text-xs text-amber-600">
                            Tất cả học sinh lớp này đã được xếp vào năm đích
                        </p>
                        @endif
                    </div>

                    {{-- Nút lưu --}}
                    @if($targetClassId && !empty($selectedStudents))
                    <div class="pt-2 border-t border-slate-100">
                        <button wire:click="saveAssignment"
                            wire:loading.attr="disabled"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5
                                   bg-primary-600 text-white rounded-xl hover:bg-primary-700
                                   disabled:opacity-60 disabled:cursor-not-allowed
                                   transition text-sm font-semibold">
                            <span wire:loading.remove wire:target="saveAssignment">
                                Xếp {{ count($selectedStudents) }} học sinh vào lớp
                            </span>
                            <span wire:loading wire:target="saveAssignment">
                                Đang lưu...
                            </span>
                        </button>
                    </div>
                    @endif
                </div>

                {{-- CỘT PHẢI: Danh sách học sinh --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-700">
                            Học sinh chưa có lớp
                            @if($availableStudents->isNotEmpty())
                            <span class="text-primary-600">({{ $availableStudents->count() }})</span>
                            @endif
                        </span>

                        @if($availableStudents->isNotEmpty())
                        <button wire:click="toggleSelectAll"
                            class="text-xs font-semibold text-primary-600 hover:text-primary-800 transition">
                            {{ count($selectedStudents) === $availableStudents->count() ? 'Bỏ chọn tất cả' : 'Chọn tất cả' }}
                        </button>
                        @endif
                    </div>

                    @if(!$sourceClassId)
                    <div class="p-8 text-center text-sm text-slate-400">
                        Chọn lớp nguồn để xem danh sách học sinh
                    </div>
                    @elseif($availableStudents->isEmpty())
                    <div class="p-8 text-center">
                        <svg class="w-10 h-10 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2 text-sm text-slate-400">Tất cả đã được xếp lớp</p>
                    </div>
                    @else
                    <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                        @foreach($availableStudents as $student)
                        @php $sid = (string) $student->id; @endphp
                        <label class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50
                                      cursor-pointer transition"
                            wire:key="student-{{ $student->id }}">
                            <input
                                type="checkbox"
                                wire:model="selectedStudents"
                                value="{{ $student->id }}"
                                class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-slate-900">
                                    {{ $student->full_name_with_saint ?? $student->last_name . ' ' . $student->first_name }}
                                </div>
                            </div>
                            @if(in_array($sid, $selectedStudents))
                            <svg class="w-4 h-4 text-primary-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            @endif
                        </label>
                        @endforeach
                    </div>

                    {{-- Footer đếm số đã chọn --}}
                    @if(!empty($selectedStudents))
                    <div class="px-5 py-3 bg-primary-50 border-t border-primary-100 text-xs text-primary-700 font-medium">
                        Đã chọn {{ count($selectedStudents) }} / {{ $availableStudents->count() }} học sinh
                    </div>
                    @endif
                    @endif
                </div>
            </div>

            {{-- Link về năm học --}}
            <div class="text-center">
                <a href="{{ route('school-years.index') }}"
                    class="text-sm text-slate-400 hover:text-slate-600 transition">
                    Hoàn tất, về quản lý năm học →
                </a>
            </div>
        </div>
        @endif

    </div>
</div>