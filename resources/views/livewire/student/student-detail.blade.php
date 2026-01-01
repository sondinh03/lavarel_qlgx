<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-6xl space-y-6">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm">
            <a href="{{ route('ds-lop') }}" class="text-blue-600 font-medium hover:text-blue-700">
                Danh sách lớp
            </a>
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-slate-600 font-medium">Hồ sơ học sinh</span>
        </nav>

        {{-- Loading --}}
        @if($isLoading)
        <div class="bg-white rounded-2xl p-12 text-center shadow-sm border border-slate-200">
            <div class="w-10 h-10 mx-auto mb-4 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-slate-600">Đang tải dữ liệu…</p>
        </div>
        @else

        {{-- CARD --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- HEADER --}}
            <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-blue-50 to-white">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex items-center gap-4 flex-1">
                        {{-- Avatar --}}
                        <div class="w-16 h-16 rounded-2xl bg-blue-500 text-white
                                    flex items-center justify-center text-2xl font-bold">
                            {{-- {{ mb_substr($student['last_name'], 0, 1) }} --}}
                        </div>

                        {{-- Name --}}
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">
                                {{ $student['full_name'] }}
                            </h1>
                            <p class="text-sm text-slate-600 mt-1">
                                Mã HS: <span class="font-semibold">{{ $student['code'] }}</span>
                            </p>
                        </div>
                    </div>

                    {{-- Status + Actions --}}
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $student['status_badge_class'] }}">
                            {{ $student['status_label'] }}
                        </span>

                        @if($isAdmin || $isDecen)
                        <button wire:click="edit"
                            class="p-2 rounded-lg hover:bg-blue-50 text-blue-600 transition"
                            title="Chỉnh sửa">
                            ✏️
                        </button>

                        <button wire:click="delete"
                            wire:confirm="Bạn có chắc chắn muốn xóa?"
                            class="p-2 rounded-lg hover:bg-red-50 text-red-600 transition"
                            title="Xóa">
                            🗑️
                        </button>
                        @endif

                        <button wire:click="printProfile"
                            class="p-2 rounded-lg hover:bg-slate-100 text-slate-600 transition"
                            title="In">
                            🖨️
                        </button>

                        <button wire:click="exportPDF"
                            class="p-2 rounded-lg hover:bg-slate-100 text-slate-600 transition"
                            title="PDF">
                            📄
                        </button>
                    </div>
                </div>
            </div>

            {{-- TABS (Segmented iOS style) --}}
            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                <div class="inline-flex rounded-xl bg-slate-200 p-1 text-sm font-medium">
                    @foreach([
                    'basic' => 'Cơ bản',
                    'baptism' => 'Rửa tội',
                    'more_power' => 'Thêm sức',
                    'other' => 'Khác'
                    ] as $key => $label)
                    <button
                        wire:click="switchTab('{{ $key }}')"
                        class="px-4 py-2 rounded-lg transition-all
                                {{ $activeTab === $key
                                    ? 'bg-white shadow text-blue-600'
                                    : 'text-slate-600 hover:text-blue-600'
                                }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- CONTENT --}}
            <div class="p-6 space-y-6 text-sm">

                {{-- BASIC --}}
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
                    {{-- <x-info-row label="Lớp giáo lý" :value="$student['giao_ly_class']" /> --}}
                    <x-info-row label="Bậc thánh" :value="$student['holy_label']" />
                </x-info-group>
                @endif

                {{-- BAPTISM --}}
                @if($activeTab === 'baptism')
                <x-info-group title="Rửa tội">
                    <x-info-row label="Ngày" :value="$student['baptism_date']" />
                    <x-info-row label="Số sổ" :value="$student['baptism_number']" />
                    <x-info-row label="Cha rửa tội" :value="$student['baptism_giver']" />
                    <x-info-row label="Người đỡ đầu" :value="$student['baptism_sponsor']" />
                    <x-info-row label="Giáo xứ" :value="$student['baptism_parish']" />
                </x-info-group>
                @endif

                {{-- MORE POWER --}}
                @if($activeTab === 'more_power')
                <x-info-group title="Thêm sức">
                    <x-info-row label="Ngày" :value="$student['more_power_date']" />
                    <x-info-row label="Số sổ" :value="$student['more_power_number']" />
                    <x-info-row label="Đức cha" :value="$student['more_power_giver']" />
                    <x-info-row label="Người đỡ đầu" :value="$student['more_power_sponsor']" />
                    <x-info-row label="Giáo xứ" :value="$student['more_power_parish']" />
                </x-info-group>
                @endif

                {{-- OTHER --}}
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