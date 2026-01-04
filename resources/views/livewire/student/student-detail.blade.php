<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#student-profile-main" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="student-profile-main" class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Danh sách lớp', 'url' => route('ds-lop')],
            ['label' => 'Hồ sơ học sinh']
        ]" separator="arrow" />

        {{-- Loading State --}}
        <x-loading.overlay wire-target="$wire" mode="centered">
            Đang tải dữ liệu học sinh...
        </x-loading.overlay>

        @if(!$isLoading)
        {{-- CARD CONTAINER --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- HEADER --}}
            <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex flex-col sm:flex-row gap-4 sm:items-center">

                    {{-- Left: Avatar + Info --}}
                    <div class="flex items-center gap-4 flex-1">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 
                                    text-white flex items-center justify-center text-2xl font-bold 
                                    shadow-sm ring-4 ring-primary-50">
                            {{ mb_substr($student['full_name'], 0, 1) }}
                        </div>

                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">
                                {{ $student['full_name'] }}
                            </h1>
                            <p class="text-sm text-slate-600 mt-1">
                                Mã HS: <span class="font-mono font-semibold text-slate-900">{{ $student['code'] }}</span>
                            </p>
                        </div>
                    </div>

                    {{-- Right: Status + Actions --}}
                    <div class="flex items-center gap-2">
                        {{-- Status Badge
                        <x-student.status-badge
                            :class="$student['status_badge_class']"
                            :label="$student['status_label']" />
                             --}}
                        {{-- Admin Actions --}}
                        @if($isAdmin || $isDecen)
                        <div class="flex items-center gap-1 border-l border-slate-200 pl-2 ml-2">
                            <button wire:click="edit"
                                class="p-2 rounded-lg text-primary-600 hover:bg-primary-50
                                       active:scale-95 transition-all"
                                title="Chỉnh sửa">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>

                            {{-- <button wire:click="delete"
                                wire:confirm="Bạn có chắc chắn muốn xóa?"
                                class="p-2 rounded-lg text-red-600 hover:bg-red-50
                                       active:scale-95 transition-all"
                                title="Xóa">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button> --}}
                        </div>
                        @endif

                        {{-- Export Actions --}}
                        <div class="flex items-center gap-1 border-l border-slate-200 pl-2 ml-2">
                            <button wire:click="printProfile"
                                class="p-2 rounded-lg text-slate-600 hover:bg-slate-100
                                       active:scale-95 transition-all"
                                title="In hồ sơ">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                            </button>

                            <button wire:click="exportPDF"
                                class="p-2 rounded-lg text-slate-600 hover:bg-slate-100
                                       active:scale-95 transition-all"
                                title="Xuất PDF">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABS --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <div class="inline-flex rounded-xl bg-slate-200 p-1 text-sm font-medium">
                    @foreach([
                    'basic' => 'Cơ bản',
                    'baptism' => 'Rửa tội',
                    'more_power' => 'Thêm sức',
                    'other' => 'Khác'
                    ] as $key => $label)
                    <button wire:click="switchTab('{{ $key }}')"
                        class="px-4 py-2 rounded-lg transition-all
                               {{ $activeTab === $key
                                   ? 'bg-white shadow-sm text-primary-600 font-semibold'
                                   : 'text-slate-600 hover:text-primary-600 hover:bg-white/50'
                               }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- CONTENT --}}
            <div class="p-6 space-y-6 text-sm">

                @if($activeTab === 'basic')
                <x-info-group title="Thông tin cá nhân">
                    <x-info-row label="Giới tính" :value="$student['sex_label']" />
                    <x-info-row label="Ngày sinh" :value="$student['birthday']" />
                    <x-info-row label="Điện thoại" :value="$student['phone']" />
                    <x-info-row label="Email" :value="$student['email']" />
                    <x-info-row label="CCCD" :value="$student['cccd']" />
                </x-info-group>

                <x-info-group title="Gia đình">
                    <x-info-row label="Cha" :value="$student['father']" />
                    <x-info-row label="Mẹ" :value="$student['mother']" />
                    <x-info-row label="Quê quán" :value="$student['origin']" />
                    <x-info-row label="Phường/Xã" :value="$student['ward']" />
                    <x-info-row label="Tỉnh/TP" :value="$student['province']" />
                </x-info-group>

                <x-info-group title="Giáo xứ & lớp">
                    <x-info-row label="Giáo phận" :value="$student['diocese']" />
                    <x-info-row label="Giáo hạt" :value="$student['deanery']" />
                    <x-info-row label="Giáo xứ" :value="$student['parish']" />
                    <x-info-row label="Lớp học" :value="$student['lop']" />
                    <x-info-row label="Bậc thánh" :value="$student['holy_label']" />
                </x-info-group>
                @endif

                @if($activeTab === 'baptism')
                <x-info-group title="Rửa tội">
                    <x-info-row label="Ngày" :value="$student['baptism_date']" />
                    <x-info-row label="Số sổ" :value="$student['baptism_number']" />
                    <x-info-row label="Cha rửa tội" :value="$student['baptism_giver']" />
                    <x-info-row label="Người đỡ đầu" :value="$student['baptism_sponsor']" />
                    <x-info-row label="Giáo xứ" :value="$student['baptism_parish']" />
                </x-info-group>
                @endif

                @if($activeTab === 'more_power')
                <x-info-group title="Thêm sức">
                    <x-info-row label="Ngày" :value="$student['more_power_date']" />
                    <x-info-row label="Số sổ" :value="$student['more_power_number']" />
                    <x-info-row label="Đức cha" :value="$student['more_power_giver']" />
                    <x-info-row label="Người đỡ đầu" :value="$student['more_power_sponsor']" />
                    <x-info-row label="Giáo xứ" :value="$student['more_power_parish']" />
                </x-info-group>
                @endif

                @if($activeTab === 'other')
                <x-info-group title="Khác">
                    <x-info-row label="Ngày hứa" :value="$student['promise_day']" />
                    <x-info-row label="Ghi chú" :value="$student['note']" />
                    <x-info-row label="Tạo lúc" :value="$student['created_at']" />
                    <x-info-row label="Cập nhật" :value="$student['updated_at']" />
                </x-info-group>
                @endif

            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush