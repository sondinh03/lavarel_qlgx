<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#student-profile-main" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="student-profile-main" class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('dashboard')],
            ['label' => 'Danh sách lớp', 'url' => route('classes.index')],
            ['label' => 'Hồ sơ học sinh', 'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\'/></svg>']
        ]" separator="arrow" />

        {{-- Toast Notifications --}}
        <div role="status" aria-live="polite">
            @if (session()->has('message'))
            <x-toast-notification type="success" :duration="3500">
                {{ session('message') }}
            </x-toast-notification>
            @endif
            @if (session()->has('error'))
            <x-toast-notification type="error" :duration="4000">
                {{ session('error') }}
            </x-toast-notification>
            @endif
        </div>

        {{-- Loading State --}}
        @if($isLoading)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12">
            <div class="flex items-center justify-center gap-3">
                <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-lg text-slate-700">Đang tải dữ liệu học sinh...</span>
            </div>
        </div>

        @elseif(empty($student))
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
            <p class="text-slate-500">Không có dữ liệu học sinh.</p>
        </div>

        @else
        {{-- CARD CONTAINER --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- HEADER --}}
            <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex flex-col sm:flex-row gap-4 sm:items-start justify-between">

                    {{-- Avatar + Info --}}
                    <div class="flex items-start gap-4 flex-1">

                        {{-- Avatar: ảnh thật hoặc chữ cái đầu --}}
                        @if($student['avatar_path'])
                        <img src="{{ asset($student['avatar_path']) }}"
                            alt="{{ $student['full_name'] }}"
                            class="w-20 h-20 rounded-2xl object-cover shadow-lg ring-4 ring-primary-50 flex-shrink-0">
                        @else
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600
                                    text-white flex items-center justify-center text-2xl font-bold
                                    shadow-lg ring-4 ring-primary-50 flex-shrink-0">
                            {{ mb_substr($student['full_name'], 0, 1, 'UTF-8') }}
                        </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <h1 class="text-2xl font-bold text-slate-900 mb-1">
                                {{ $student['full_name'] }}
                            </h1>
                            <div class="flex flex-wrap items-center gap-3 text-sm">
                                <span class="text-slate-600">
                                    Mã HS:
                                    <span class="font-mono font-semibold text-slate-900">
                                        {{ $student['student_code'] }}
                                    </span>
                                </span>

                                <span class="text-slate-300">|</span>

                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                             {{ $student['status_badge_class'] }}">
                                    {{ $student['status_label'] }}
                                </span>

                                @if($student['current_class'])
                                <span class="text-slate-300">|</span>
                                <span class="text-slate-600">
                                    Lớp hiện tại:
                                    <span class="font-semibold text-slate-900">{{ $student['current_class'] }}</span>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if($isAdmin || $isDecen)
                        <button wire:click="edit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                                   bg-primary-600 text-white font-semibold
                                   hover:bg-primary-700 active:scale-95 transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span class="hidden sm:inline">Chỉnh sửa</span>
                        </button>
                        @endif

                        {{-- Export Dropdown --}}
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                                       bg-white border border-slate-200 text-slate-700 font-semibold
                                       hover:bg-slate-50 active:scale-95 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="hidden sm:inline">Xuất file</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-cloak @click.away="open = false" x-transition
                                class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-200 py-2 z-10">

                                <button wire:click="printProfile"
                                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    In hồ sơ
                                </button>

                                <button wire:click="exportPDF"
                                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    Xuất PDF
                                </button>

                                <div class="border-t border-slate-100 my-1"></div>

                                <button wire:click="exportLyLichCanhan"
                                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Lý lịch học sinh (Word)
                                </button>

                                <button wire:click="exportBiTich"
                                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Bí tích (Word)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABS --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <div class="inline-flex rounded-xl bg-slate-200 p-1 text-sm font-medium">
                    <button wire:click="switchTab('basic')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg transition-all
                               {{ $activeTab === 'basic'
                                   ? 'bg-white shadow-sm text-primary-600 font-semibold'
                                   : 'text-slate-600 hover:text-primary-600 hover:bg-white/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Thông tin cơ bản
                    </button>

                    <button wire:click="switchTab('history')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg transition-all
                               {{ $activeTab === 'history'
                                   ? 'bg-white shadow-sm text-primary-600 font-semibold'
                                   : 'text-slate-600 hover:text-primary-600 hover:bg-white/50' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Lịch sử học tập
                        @if(count($student['class_history']) > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-semibold
                                     rounded-full bg-primary-100 text-primary-700">
                            {{ count($student['class_history']) }}
                        </span>
                        @endif
                    </button>
                </div>
            </div>

            {{-- CONTENT --}}
            <div class="p-6 space-y-6">

                {{-- ====== TAB: THÔNG TIN CƠ BẢN ====== --}}
                @if($activeTab === 'basic')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Thông tin cá nhân --}}
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Thông tin cá nhân
                        </h3>
                        <div class="space-y-3">
                            <x-info-row label="Họ và tên" :value="$student['full_name']" />
                            <x-info-row label="Giới tính" :value="$student['gender_label']" />
                            <x-info-row label="Ngày sinh" :value="$student['birthday']" />
                            <x-info-row label="Điện thoại" :value="$student['phone']" />
                            <x-info-row label="Email" :value="$student['email']" />
                            @if($student['cccd'])
                            <x-info-row label="CCCD" :value="$student['cccd']" />
                            @endif
                            @if($student['address'])
                            <x-info-row label="Địa chỉ" :value="$student['address']" />
                            @endif
                        </div>
                    </div>

                    {{-- Gia đình --}}
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Gia đình
                        </h3>
                        <div class="space-y-3">
                            <x-info-row label="Tên cha" :value="$student['father_name']" />
                            <x-info-row label="Tên mẹ" :value="$student['mother_name']" />
                        </div>
                    </div>

                    {{-- Giáo xứ --}}
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Giáo xứ
                        </h3>
                        <div class="space-y-3">
                            <x-info-row label="Giáo xứ" :value="$student['parish']" />
                            <x-info-row label="Giáo họ" :value="$student['parish_group']" />
                            <x-info-row label="Thánh bổn mạng" :value="$student['saint_name']" />
                        </div>
                    </div>

                    {{-- Thông tin bổ sung --}}
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Thông tin bổ sung
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-1">Ghi chú</label>
                                <div class="text-sm text-slate-700 bg-white rounded-lg p-3 border border-slate-200 min-h-[60px]">
                                    {{ $student['note'] ?: 'Không có ghi chú' }}
                                </div>
                            </div>
                            <x-info-row label="Ngày tạo" :value="$student['created_at']" />
                            <x-info-row label="Cập nhật lần cuối" :value="$student['updated_at']" />
                        </div>
                    </div>

                </div>
                @endif

                {{-- ====== TAB: LỊCH SỬ HỌC TẬP ====== --}}
                @if($activeTab === 'history')
                <div class="max-w-3xl mx-auto">
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Quá trình học tập ({{ count($student['class_history']) }} lớp)
                        </h3>

                        @if(count($student['class_history']) > 0)
                        <div class="space-y-3">
                            @foreach($student['class_history'] as $i => $class)
                            <div class="flex items-center gap-4 p-4 bg-white rounded-xl border border-slate-200 hover:border-primary-200 transition-colors">

                                {{-- Index --}}
                                <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 
                                            flex items-center justify-center text-sm font-bold flex-shrink-0">
                                    {{ $i + 1 }}
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-slate-900">
                                        {{ $class['class_name'] }}
                                        @if($class['class_symbol'])
                                        <span class="ml-1 font-mono text-xs text-slate-500">({{ $class['class_symbol'] }})</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                                        @if($class['school_year'])
                                        <span>{{ $class['school_year'] }}</span>
                                        @endif
                                        @if($class['joined_at'])
                                        <span class="text-slate-300">•</span>
                                        <span>Tham gia: {{ $class['joined_at'] }}</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Badge lớp đầu tiên (hiện tại) --}}
                                @if($i === 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                             bg-green-100 text-green-700 flex-shrink-0">
                                    Hiện tại
                                </span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-8">
                            <svg class="mx-auto w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <p class="mt-3 text-sm text-slate-500 italic">Chưa có lịch sử học tập</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

            </div>
        </div>
        @endif

    </div>
</div>

@push('styles')
<style>
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
    }
</style>
@endpush

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('print-profile', () => {
            window.print();
        });
    });
</script>
@endpush