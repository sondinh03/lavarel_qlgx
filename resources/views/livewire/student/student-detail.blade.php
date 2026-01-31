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
        @else
        {{-- CARD CONTAINER --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- HEADER --}}
            <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex flex-col sm:flex-row gap-4 sm:items-start justify-between">

                    {{-- Left: Avatar + Info --}}
                    <div class="flex items-start gap-4 flex-1">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 
                                    text-white flex items-center justify-center text-2xl font-bold 
                                    shadow-lg ring-4 ring-primary-50 flex-shrink-0">
                            {{ mb_substr($student['full_name'], 0, 1, 'UTF-8') }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <h1 class="text-2xl font-bold text-slate-900 mb-1">
                                {{ $student['full_name'] }}
                            </h1>
                            <div class="flex flex-wrap items-center gap-3 text-sm">
                                <span class="text-slate-600">
                                    Mã HS: <span class="font-mono font-semibold text-slate-900">{{ $student['code'] }}</span>
                                </span>
                                <span class="text-slate-300">|</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $student['status_badge_class'] }}">
                                    {{ $student['status_label'] }}
                                </span>
                                @if($student['lop_name'])
                                <span class="text-slate-300">|</span>
                                <span class="text-slate-600">
                                    Lớp: <span class="font-semibold text-slate-900">{{ $student['lop_name'] }}</span>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Right: Actions --}}
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

                        {{-- Export Menu --}}
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

            {{-- ✅ TABS - CHỈ 4 TAB --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 overflow-x-auto">
                <div class="inline-flex rounded-xl bg-slate-200 p-1 text-sm font-medium whitespace-nowrap">
                    @foreach([
                    'basic' => ['label' => 'Cơ bản', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    'baptism' => ['label' => 'Rửa tội', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                    'more_power' => ['label' => 'Thêm sức', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                    'other' => ['label' => 'Khác', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z']
                    ] as $key => $tab)
                    <button wire:click="switchTab('{{ $key }}')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg transition-all
                               {{ $activeTab === $key
                                   ? 'bg-white shadow-sm text-primary-600 font-semibold'
                                   : 'text-slate-600 hover:text-primary-600 hover:bg-white/50'
                               }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}" />
                        </svg>
                        {{ $tab['label'] }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- CONTENT --}}
            <div class="p-6 space-y-6">

                {{-- TAB: Thông tin cơ bản --}}
                @if($activeTab === 'basic')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Thông tin cá nhân --}}
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Thông tin cá nhân
                        </h3>
                        <div class="space-y-3">
                            <x-info-row label="Họ tên đầy đủ" :value="$student['full_name']" />
                            <x-info-row label="Giới tính" :value="$student['sex_label']" />
                            <x-info-row label="Ngày sinh" :value="$student['birthday']" />
                            <x-info-row label="Điện thoại" :value="$student['phone']" />
                            <x-info-row label="Email" :value="$student['email']" />
                            <x-info-row label="CCCD" :value="$student['cccd']" />
                        </div>
                    </div>

                    {{-- Gia đình --}}
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Gia đình & Nguyên quán
                        </h3>
                        <div class="space-y-3">
                            <x-info-row label="Cha" :value="$student['father']" />
                            <x-info-row label="Mẹ" :value="$student['mother']" />
                            <x-info-row label="Nguyên quán" :value="$student['origin']" />
                            <x-info-row label="Phường/Xã" :value="$student['ward']" />
                            <x-info-row label="Tỉnh/TP" :value="$student['province']" />
                        </div>
                    </div>

                    {{-- Giáo xứ --}}
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Giáo xứ & Giáo họ
                        </h3>
                        <div class="space-y-3">
                            <x-info-row label="Giáo phận" :value="$student['diocese']" />
                            <x-info-row label="Giáo hạt" :value="$student['deanery']" />
                            <x-info-row label="Giáo xứ" :value="$student['parish']" />
                            <x-info-row label="Giáo họ" :value="$student['paid']" />
                            <x-info-row label="Bậc thánh" :value="$student['holy_name']" />
                        </div>
                    </div>

                    {{-- ✅ Lớp học & Lịch sử --}}
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Quá trình học tập
                        </h3>

                        @if(isset($student['class_history']) && count($student['class_history']) > 0)
                        <div class="space-y-2">
                            @foreach($student['class_history'] as $class)
                            <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-slate-200">
                                <div class="flex-1">
                                    <div class="font-semibold text-slate-900">{{ $class['class_name'] }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        {{ $class['school_year'] }} • Tham gia: {{ $class['joined_at'] }}
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $class['status_class'] }}">
                                    {{ $class['status_label'] }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-sm text-slate-500 italic">Chưa có lịch sử học tập</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- TAB: Rửa tội --}}
                @if($activeTab === 'baptism')
                <div class="max-w-3xl mx-auto">
                    <div class="bg-gradient-to-br from-blue-50 to-white rounded-xl p-6 border border-blue-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-blue-500 text-white flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Bí tích Rửa tội</h3>
                                <p class="text-sm text-slate-600">Thông tin về bí tích Rửa tội</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-info-row label="Ngày rửa tội" :value="$student['baptism_date']" />
                            <x-info-row label="Số sổ" :value="$student['baptism_number']" />
                            <x-info-row label="Cha ban bí tích" :value="$student['baptism_giver']" />
                            <x-info-row label="Người đỡ đầu" :value="$student['baptism_sponsor']" />
                            <div class="md:col-span-2">
                                <x-info-row label="Nơi rửa tội" :value="$student['baptism_full_location']" />
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- TAB: Thêm sức --}}
                @if($activeTab === 'more_power')
                <div class="max-w-3xl mx-auto">
                    <div class="bg-gradient-to-br from-yellow-50 to-white rounded-xl p-6 border border-yellow-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-yellow-500 text-white flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Bí tích Thêm sức</h3>
                                <p class="text-sm text-slate-600">Thông tin về bí tích Thêm sức</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-info-row label="Ngày thêm sức" :value="$student['more_power_date']" />
                            <x-info-row label="Số sổ" :value="$student['more_power_number']" />
                            <x-info-row label="Đức cha ban bí tích" :value="$student['more_power_giver']" />
                            <x-info-row label="Người đỡ đầu" :value="$student['more_power_sponsor']" />
                            <div class="md:col-span-2">
                                <x-info-row label="Nơi thêm sức" :value="$student['more_power_full_location']" />
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- TAB: Thông tin khác --}}
                @if($activeTab === 'other')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Giáo dục & nghề nghiệp --}}
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Giáo dục & nghề nghiệp
                        </h3>
                        <div class="space-y-3">
                            <x-info-row label="Dân tộc" :value="$student['ethnic']" />
                            <x-info-row label="Trình độ" :value="$student['level']" />
                            <x-info-row label="Nghề nghiệp" :value="$student['career']" />
                            <x-info-row label="Chức vụ" :value="$student['position']" />
                            <x-info-row label="Trình độ chuyên môn" :value="$student['professional_level']" />
                            <x-info-row label="Ngôn ngữ" :value="$student['language']" />
                        </div>
                    </div>

                    {{-- Thông tin khác --}}
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Thông tin bổ sung
                        </h3>
                        <div class="space-y-3">
                            <x-info-row label="Ngày hứa" :value="$student['promise_day']" />
                            <div class="pt-2">
                                <label class="block text-sm font-medium text-slate-600 mb-1">Ghi chú</label>
                                <div class="text-sm text-slate-700 bg-white rounded-lg p-3 border border-slate-200">
                                    {{ $student['note'] ?: 'Không có ghi chú' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hệ thống --}}
                    <div class="bg-slate-50 rounded-xl p-5 border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Thông tin hệ thống
                        </h3>
                        <div class="space-y-3">
                            <x-info-row label="Ngày tạo" :value="$student['created_at']" />
                            <x-info-row label="Cập nhật lần cuối" :value="$student['updated_at']" />
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
        @endif
    </div>
</div>

{{-- Print Styles --}}
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

{{-- Alpine.js for dropdown --}}
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