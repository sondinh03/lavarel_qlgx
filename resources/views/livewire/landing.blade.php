<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-6xl space-y-6">

        {{-- Navbar --}}
        <header class="flex items-center justify-between gap-3 max-w-4xl mx-auto">
            <a href="{{ route('landing') }}" class="flex items-center gap-2.5 min-w-0">
                <img src="{{ url(config('settings.logo')) }}"
                    class="h-10 w-auto flex-shrink-0"
                    alt="{{ config('settings.web_name') }}">
                <span class="font-bold text-slate-900 truncate text-sm sm:text-base hidden sm:block">
                    {{ config('settings.web_name', 'Hệ thống Quản lý Giáo lý') }}
                </span>
            </a>
            <a href="{{ route('login') }}"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5
                    text-sm font-bold text-white
                    bg-primary-600 rounded-xl
                    shadow-sm hover:bg-primary-700
                    hover:shadow-md transition-all flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                <span class="sm:hidden">Đăng nhập</span>
                <span class="hidden sm:inline">Đăng nhập hệ thống</span>
            </a>
        </header>

        {{-- Hero --}}
        <div class="text-center max-w-2xl mx-auto pt-2 pb-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">
                {{ config('settings.web_name', 'Hệ thống Quản lý Giáo lý') }}
            </h1>
            <p class="mt-2 text-base sm:text-lg text-slate-600">
                Tra cứu kết quả giáo lý và quản lý lớp học trực tuyến
            </p>
        </div>

        {{-- Toast Notifications --}}
        <div role="status" aria-live="polite" class="fixed top-4 right-4 z-50 space-y-2">
            @if (session()->has('message'))
            <x-toast-notification type="success" :duration="3500">
                {{ session('message') }}
            </x-toast-notification>
            @endif

            @if (session()->has('info'))
            <x-toast-notification type="info" :duration="3500">
                {{ session('info') }}
            </x-toast-notification>
            @endif
        </div>

        {{-- Main content --}}
        <div class="space-y-6 max-w-md mx-auto">

            {{-- PHỤ HUYNH TRA CỨU --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                {{-- Card Header --}}
                <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Tra cứu kết quả giáo lý</h2>
                            <p class="text-sm text-slate-600 mt-0.5">Dành cho phụ huynh và học viên</p>
                        </div>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-6 space-y-4">

                    {{-- Input SĐT --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Số điện thoại phụ huynh <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <input
                                wire:model.defer="phone"
                                wire:keydown.enter="search"
                                type="tel"
                                placeholder="VD: 0901234567"
                                class="flex-1 px-4 py-3 rounded-xl border border-slate-300
                           focus:outline-none focus:ring-2 focus:ring-primary-500
                           @error('phone') border-red-500 @enderror"
                                autofocus />

                            <button
                                wire:click="search"
                                wire:loading.attr="disabled"
                                class="px-5 py-3 bg-primary-600 text-white rounded-xl
                           hover:bg-primary-700 transition font-semibold
                           disabled:opacity-50 disabled:cursor-not-allowed
                           flex items-center gap-2">
                                <svg wire:loading.remove wire:target="search"
                                    class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <svg wire:loading wire:target="search"
                                    class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                <span wire:loading.remove wire:target="search">Tra cứu</span>
                                <span wire:loading wire:target="search">Đang tìm...</span>
                            </button>
                        </div>

                        @error('phone')
                        <p class="mt-1.5 text-sm text-red-600 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Nút tìm lại --}}
                    @if($searched)
                    <div>
                        <button wire:click="resetSearch"
                            class="text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Tìm lại
                        </button>
                    </div>
                    @endif

                    {{-- Error --}}
                    @if($error)
                    <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-red-700 font-medium">{{ $error }}</p>
                        </div>
                    </div>
                    @endif

                    @if(count($results) > 0)
                    <div class="space-y-2">
                        <p class="text-sm font-semibold text-slate-600">
                            Tìm thấy <span class="text-primary-600">{{ count($results) }}</span> học viên:
                        </p>

                        @foreach($results as $student)
                        <button
                            wire:click="viewStudent({{ $student['id'] }})"
                            class="w-full flex items-center gap-3 px-4 py-3
               bg-slate-50 hover:bg-primary-50 border border-slate-200
               hover:border-primary-300 rounded-xl transition-all text-left
               {{ $viewingStudentId === $student['id'] ? 'border-primary-400 bg-primary-50 ring-1 ring-primary-300' : '' }}">

                            {{-- Avatar --}}
                            @if($student['avatar_path'])
                            <img src="{{ asset($student['avatar_path']) }}"
                                class="w-10 h-10 rounded-xl object-cover flex-shrink-0"
                                alt="{{ $student['full_name'] }}">
                            @else
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600
                    text-white flex items-center justify-center text-base font-bold flex-shrink-0">
                                {{ mb_substr($student['full_name'], 0, 1, 'UTF-8') }}
                            </div>
                            @endif

                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-slate-900 truncate text-sm">
                                    {{ $student['full_name_with_saint'] }}
                                </p>
                                <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                                    <span class="font-mono">{{ $student['student_code'] }}</span>
                                    @if($student['current_class'])
                                    <span>•</span>
                                    <span class="text-primary-600 font-medium">{{ $student['current_class'] }}</span>
                                    @endif
                                </div>
                            </div>

                            <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                        @endforeach
                    </div>
                    @endif

                    {{-- Hướng dẫn (chỉ hiển thị khi chưa có kết quả) --}}
                    @if(!$searched)
                    <div class="bg-primary-50 border border-primary-200 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-primary-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-sm text-primary-700 space-y-1">
                                <p class="font-semibold">Hướng dẫn tra cứu:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs ml-1">
                                    <li>Nhập số điện thoại đã đăng ký khi nhập học</li>
                                    <li>Một số điện thoại có thể có nhiều học viên</li>
                                    <li>Liên hệ Ban quản lý nếu không tìm thấy thông tin</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            {{-- ĐĂNG KÝ SỔ GIA ĐÌNH (compact) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-dashed border-emerald-200 overflow-hidden">
                <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold text-slate-900">Đăng ký sổ gia đình</h2>
                            <p class="text-sm text-slate-600 mt-0.5">Khai báo hộ, thành viên, hôn phối và bí tích</p>
                        </div>
                    </div>
                    <a href="{{ route('parishioners.register.public') }}"
                        class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-emerald-500 text-white text-sm font-semibold hover:opacity-90 transition whitespace-nowrap sm:flex-shrink-0">
                        Mở form đăng ký
                    </a>
                </div>
            </div>
        </div>

        {{-- ==================== CHI TIẾT HỌC SINH ==================== --}}
        @if($viewingStudent)
        <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex items-center gap-4">
                    {{-- Nút quay lại (chỉ hiện khi có nhiều học sinh) --}}
                    @if(count($results) > 1)
                    <button wire:click="backToList"
                        class="p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    @endif

                    {{-- Avatar --}}
                    @if($viewingStudent['avatar_path'])
                    <img src="{{ asset($viewingStudent['avatar_path']) }}"
                        class="w-12 h-12 rounded-2xl object-cover flex-shrink-0"
                        alt="{{ $viewingStudent['full_name'] }}">
                    @else
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600
                        text-white flex items-center justify-center text-lg font-bold flex-shrink-0">
                        {{ mb_substr($viewingStudent['full_name'], 0, 1, 'UTF-8') }}
                    </div>
                    @endif

                    <div class="flex-1 min-w-0">
                        <h2 class="text-xl font-bold text-slate-900 truncate">
                            {{ $viewingStudent['full_name_with_saint'] }}
                        </h2>
                        <div class="flex items-center gap-2 text-sm text-slate-500 mt-0.5">
                            <span class="font-mono">{{ $viewingStudent['student_code'] }}</span>
                            <span>•</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $viewingStudent['status_badge_class'] }}">
                                {{ $viewingStudent['status_label'] }}
                            </span>
                            @if($viewingStudent['current_class'])
                            <span>•</span>
                            <span class="font-medium text-primary-600">{{ $viewingStudent['current_class'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                <div class="inline-flex w-full rounded-xl bg-slate-200 p-1 text-sm font-medium">
                    <button wire:click="switchTab('info')"
                        class="flex-1 py-2 rounded-lg transition-all
                       {{ $activeTab === 'info'
                           ? 'bg-white shadow-sm text-primary-600 font-semibold'
                           : 'text-slate-600 hover:text-slate-900' }}">
                        Hồ sơ
                    </button>
                    <button wire:click="switchTab('attendance')"
                        class="flex-1 py-2 rounded-lg transition-all
                       {{ $activeTab === 'attendance'
                           ? 'bg-white shadow-sm text-primary-600 font-semibold'
                           : 'text-slate-600 hover:text-slate-900' }}">
                        Điểm danh
                    </button>
                    <button wire:click="switchTab('scores')"
                        class="flex-1 py-2 rounded-lg transition-all
                       {{ $activeTab === 'scores'
                           ? 'bg-white shadow-sm text-primary-600 font-semibold'
                           : 'text-slate-600 hover:text-slate-900' }}">
                        Kết quả học tập
                    </button>
                </div>
            </div>

            {{-- Tab Content --}}
            <div class="p-4 sm:p-6">

                {{-- ====== TAB: HỒ SƠ ====== --}}
                @if($activeTab === 'info')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                        <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Thông tin cá nhân
                        </h3>
                        <div class="space-y-2 text-sm">
                            @foreach([
                            ['Tên thánh', $viewingStudent['saint_name']],
                            ['Họ và tên', $viewingStudent['full_name']],
                            ['Ngày sinh', $viewingStudent['birthday']],
                            ['Giới tính', $viewingStudent['gender_label']],
                            ] as [$label, $value])
                            <div class="flex justify-between py-1 border-b border-slate-100 last:border-0">
                                <span class="text-slate-500">{{ $label }}</span>
                                <span class="font-medium text-slate-800">{{ $value ?: '—' }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                        <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Gia đình & Giáo xứ
                        </h3>
                        <div class="space-y-2 text-sm">
                            @foreach([
                            ['Họ tên bố', $viewingStudent['father_name']],
                            ['Họ tên mẹ', $viewingStudent['mother_name']],
                            ['Giáo xứ', $viewingStudent['parish']],
                            ['Giáo họ', $viewingStudent['parish_group']],
                            ] as [$label, $value])
                            <div class="flex justify-between py-1 border-b border-slate-100 last:border-0">
                                <span class="text-slate-500">{{ $label }}</span>
                                <span class="font-medium text-slate-800">{{ $value ?: '—' }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Lịch sử lớp học --}}
                    @if(count($viewingStudent['class_history']) > 0)
                    <div class="sm:col-span-2 bg-slate-50 rounded-xl p-4 border border-slate-200">
                        <h3 class="text-sm font-bold text-slate-700 mb-3">
                            Lịch sử lớp học ({{ count($viewingStudent['class_history']) }} lớp)
                        </h3>
                        <div class="space-y-2">
                            @foreach($viewingStudent['class_history'] as $i => $class)
                            <div class="flex items-center gap-3 bg-white rounded-lg px-3 py-2 border border-slate-200 text-sm">
                                <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-700
                                     flex items-center justify-center text-xs font-bold flex-shrink-0">
                                    {{ $i + 1 }}
                                </span>
                                <span class="flex-1 font-medium text-slate-800">{{ $class['class_name'] }}</span>
                                <span class="text-xs text-slate-400">{{ $class['school_year'] }}</span>
                                @if($i === 0)
                                <span class="text-xs font-semibold text-primary-600 bg-primary-50 px-2 py-0.5 rounded-full">
                                    Hiện tại
                                </span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                {{-- ====== TAB: ĐIỂM DANH ====== --}}
                @if($activeTab === 'attendance')
                @if(empty($attendanceSummary))
                <div class="text-center py-10">
                    <svg class="mx-auto w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
                    </svg>
                    <p class="mt-3 text-sm text-slate-400">Chưa có dữ liệu điểm danh năm học này</p>
                </div>
                @else
                <div class="space-y-5">
                    {{-- Tiêu đề năm học --}}
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                        <h3 class="text-base font-bold text-slate-800">
                            Năm học {{ $attendanceSummary['year_name'] }}
                        </h3>
                    </div>

                    @foreach($attendanceSummary['data'] as $typeLabel => $semesters)
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

                        {{-- Header loại điểm danh --}}
                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                            <span class="text-sm font-bold text-slate-700">{{ $typeLabel }}</span>
                        </div>

                        <div class="p-4 space-y-5">
                            @foreach($semesters as $semLabel => $sessions)
                            @php
                            $present = collect($sessions)->where('status', 1)->count();
                            $phep = collect($sessions)->where('status', 2)->count();
                            $vang = collect($sessions)->where('status', 3)->count();
                            $total = count($sessions);
                            $rate = $total > 0 ? round($present / $total * 100) : 0;
                            @endphp

                            <div>
                                {{-- Semester header + tổng kết --}}
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">
                                        {{ $semLabel }}
                                    </span>
                                    <div class="flex items-center gap-2 text-xs font-semibold">
                                        <span class="text-green-600">✓ {{ $present }}</span>
                                        <span class="text-yellow-600">P {{ $phep }}</span>
                                        <span class="text-red-600">✕ {{ $vang }}</span>
                                        <span class="text-slate-400">/ {{ $total }}</span>
                                        <span class="px-2 py-0.5 rounded-full
                            {{ $rate >= 80 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                            {{ $rate }}%
                                        </span>
                                    </div>
                                </div>

                                {{-- Các ô buổi --}}
                                <div class="flex flex-wrap gap-2">
                                    @foreach($sessions as $session)
                                    @php
                                    $dotClass = match($session['status']) {
                                    1 => 'bg-green-500 text-white',
                                    2 => 'bg-yellow-400 text-slate-900',
                                    3 => 'bg-red-500 text-white',
                                    default => 'bg-slate-200 text-slate-500',
                                    };
                                    $label = match($session['status']) {
                                    1 => '✓', 2 => 'P', 3 => '✕', default => '?',
                                    };
                                    @endphp
                                    <div class="flex flex-col items-center gap-1 group relative">
                                        <span class="text-[10px] text-slate-400 leading-none">
                                            {{ \Carbon\Carbon::parse($session['date'])->format('d/m') }}
                                        </span>
                                        <span class="w-8 h-8 rounded-lg flex items-center justify-center
                                     text-xs font-bold {{ $dotClass }}">
                                            {{ $label }}
                                        </span>
                                        {{-- Tooltip lý do --}}
                                        @if($session['note'])
                                        <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2
                                    hidden group-hover:block w-36 p-2
                                    bg-slate-900 text-white text-[10px] rounded-lg z-10 shadow-xl
                                    pointer-events-none">
                                            {{ $session['note'] }}
                                            <div class="absolute left-1/2 -translate-x-1/2 top-full w-0 h-0
                                        border-l-4 border-r-4 border-t-4
                                        border-l-transparent border-r-transparent border-t-slate-900"></div>
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
                @endif

                {{-- ====== TAB: KẾT QUẢ HỌC TẬP ====== --}}
                @if($activeTab === 'scores')
                @if(empty($scoresSummary))
                <div class="text-center py-10">
                    <svg class="mx-auto w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3" />
                    </svg>
                    <p class="mt-3 text-sm text-slate-400">Chưa có dữ liệu điểm</p>
                </div>
                @else
                <div class="space-y-6">
                    @foreach($scoresSummary as $yearName => $classes)
                    <div>
                        <h3 class="text-base font-bold text-slate-800 mb-3 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                            Năm học {{ $yearName }}
                        </h3>

                        @foreach($classes as $className => $semesters)
                        <div class="mb-4 bg-white rounded-xl border border-slate-200 overflow-hidden">
                            <div class="px-4 py-2 bg-slate-50 border-b border-slate-100">
                                <span class="text-sm font-semibold text-slate-700">Lớp: {{ $className }}</span>
                            </div>

                            <div class="p-4 space-y-4">
                                @foreach($semesters as $sem => $data)
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wide">
                                            Học kỳ {{ $sem }}
                                        </span>
                                        @if($data['avg'] !== null)
                                        <span class="px-3 py-1 rounded-full text-sm font-bold
                                    {{ $data['avg'] >= 8 ? 'bg-emerald-100 text-emerald-700'
                                        : ($data['avg'] >= 5 ? 'bg-primary-100 text-primary-700'
                                        : 'bg-red-100 text-red-600') }}">
                                            TB: {{ $data['avg'] }}
                                        </span>
                                        @endif
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="text-xs text-slate-400 border-b border-slate-100">
                                                    <th class="text-left py-1 font-medium">Loại điểm</th>
                                                    <th class="text-center py-1 font-medium">Hệ số</th>
                                                    <th class="text-center py-1 font-medium">Điểm</th>
                                                    <th class="text-center py-1 font-medium">Tối đa</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-50">
                                                @foreach($data['scores'] as $score)
                                                <tr>
                                                    <td class="py-1.5 text-slate-700">{{ $score['type_name'] }}</td>
                                                    <td class="py-1.5 text-center text-slate-500">{{ $score['coefficient'] }}</td>
                                                    <td class="py-1.5 text-center">
                                                        @if($score['value'] !== null)
                                                        <span class="font-bold
                                                    {{ $score['value'] >= $score['max_score']*0.8 ? 'text-emerald-600'
                                                        : ($score['value'] >= $score['max_score']*0.5 ? 'text-primary-600'
                                                        : 'text-red-500') }}">
                                                            {{ $score['value'] }}
                                                        </span>
                                                        @else
                                                        <span class="text-slate-300">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-1.5 text-center text-slate-400">{{ $score['max_score'] }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
                @endif
                @endif

            </div>
        </div>
        @endif
    </div>
</div>

{{-- Loading Overlay --}}
<div wire:loading.delay class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl p-6 flex items-center gap-4">
        <svg class="animate-spin h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
        <span class="text-lg font-semibold text-slate-700">Đang xử lý...</span>
    </div>
</div>