<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-6xl space-y-6">

        {{-- Header with Login Button --}}
        <div class="relative py-8">
            {{-- Logo & Title --}}
            <div class="text-center">
                <div class="flex justify-center mb-4">
                    <img src="{{ url(config('settings.logo')) }}"
                        class="h-20 w-auto"
                        alt="{{ config('settings.web_name') }}">
                </div>

                <h1 class="text-3xl sm:text-4xl font-bold text-slate-900">
                    {{ config('settings.web_name', 'Hệ thống Quản lý Giáo lý') }}
                </h1>
                <p class="mt-3 text-lg text-slate-600">
                    Tra cứu kết quả giáo lý và quản lý lớp học trực tuyến
                </p>
            </div>
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

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

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
                            <h2 class="text-lg font-bold text-slate-900">
                                Tra cứu kết quả giáo lý
                            </h2>
                            <p class="text-sm text-slate-600 mt-0.5">
                                Dành cho phụ huynh và học viên
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-6 space-y-4">
                    {{-- Mã học viên --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Mã học viên <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model.defer="student_code"
                            type="text"
                            placeholder="Nhập mã học viên (VD: HS001)"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500
                                   @error('student_code') border-red-500 @enderror"
                            autofocus />
                        @error('student_code')
                        <p class="mt-1.5 text-sm text-red-600 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Ngày sinh --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Ngày sinh <span class="text-red-500">*</span>
                        </label>
                        <input
                            wire:model.defer="birthday"
                            type="date"
                            max="{{ date('Y-m-d') }}"
                            class="w-full px-4 py-3 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500
                                   @error('birthday') border-red-500 @enderror" />
                        @error('birthday')
                        <p class="mt-1.5 text-sm text-red-600 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-3 pt-2">
                        <button
                            wire:click="search"
                            wire:loading.attr="disabled"
                            class="flex-1 px-4 py-3 bg-primary-600 text-white rounded-xl
                                   hover:bg-primary-700 transition font-semibold
                                   disabled:opacity-50 disabled:cursor-not-allowed
                                   flex items-center justify-center gap-2">
                            <svg wire:loading.remove wire:target="search"
                                class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <svg wire:loading wire:target="search"
                                class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span wire:loading.remove wire:target="search">Tra cứu</span>
                            <span wire:loading wire:target="search">Đang tìm...</span>
                        </button>

                        @if($result || $error)
                        <button
                            wire:click="resetSearch"
                            class="px-4 py-3 bg-slate-200 text-slate-700 rounded-xl
                                   hover:bg-slate-300 transition font-semibold
                                   flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Tìm lại
                        </button>
                        @endif
                    </div>

                    {{-- Error Message --}}
                    @if($error)
                    <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4 animate-shake">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-red-700 font-medium">
                                {{ $error }}
                            </p>
                        </div>
                    </div>
                    @endif

                    {{-- Success Result --}}
                    @if($result)
                    <div class="bg-emerald-50 border-l-4 border-emerald-500 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1 space-y-2">
                                <h3 class="text-sm font-bold text-emerald-900">
                                    Tìm thấy học viên
                                </h3>

                                <div class="space-y-1.5 text-sm">
                                    <p class="flex justify-between">
                                        <span class="font-semibold text-slate-700">Họ tên:</span>
                                        <span class="text-slate-900">{{ $result['student']->name }}</span>
                                    </p>
                                    <p class="flex justify-between">
                                        <span class="font-semibold text-slate-700">Mã HS:</span>
                                        <span class="text-slate-900 font-mono">{{ $result['student']->code }}</span>
                                    </p>
                                    <p class="flex justify-between">
                                        <span class="font-semibold text-slate-700">Năm học:</span>
                                        <span class="text-slate-900">
                                            {{ $result['schoolYear']?->name ?? 'Chưa có năm học' }}
                                        </span>
                                    </p>
                                </div>

                                <div class="pt-3 border-t border-emerald-200">
                                    <a href="#"
                                        class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                                        Xem chi tiết kết quả học tập
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Helper Text --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-sm text-blue-700 space-y-1">
                                <p class="font-semibold">Hướng dẫn tra cứu:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs ml-1">
                                    <li>Nhập mã học viên được cấp khi đăng ký</li>
                                    <li>Chọn ngày sinh chính xác của học viên</li>
                                    <li>Liên hệ Ban quản lý nếu quên mã học viên</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- NỘI BỘ - GIÁO LÝ VIÊN --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                {{-- Card Header --}}
                <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-br from-slate-50 to-white">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">
                                Dành cho Giáo lý viên
                            </h2>
                            <p class="text-sm text-slate-600 mt-0.5">
                                Quản trị lớp học và học sinh
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="p-6 flex flex-col justify-between" style="min-height: 400px;">
                    {{-- Features List --}}
                    <div class="space-y-4">
                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-slate-50 transition">
                            <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-900">Quản lý lớp học</h3>
                                <p class="text-sm text-slate-600 mt-0.5">Tạo và quản lý lớp học, phân công giáo lý viên</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-slate-50 transition">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-900">Quản lý học sinh</h3>
                                <p class="text-sm text-slate-600 mt-0.5">Theo dõi danh sách học sinh và thông tin cá nhân</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-slate-50 transition">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-900">Nhập điểm & điểm danh</h3>
                                <p class="text-sm text-slate-600 mt-0.5">Ghi nhận kết quả học tập và chuyên cần</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-slate-50 transition">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-900">Báo cáo & thống kê</h3>
                                <p class="text-sm text-slate-600 mt-0.5">Xuất báo cáo theo năm học và học kỳ</p>
                            </div>
                        </div>
                    </div>

                    {{-- Login Button --}}
                    <div class="mt-6 space-y-3">
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 
                          bg-white border-2 border-primary-600 text-primary-600
                          rounded-xl hover:bg-primary-600 hover:text-white
                          transition-all font-semibold shadow-sm hover:shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span class="hidden sm:inline">Đăng nhập</span>
                        </a>

                        <p class="text-xs text-center text-slate-500">
                            Dành cho Giáo lý viên và Ban quản lý
                        </p>
                    </div>
                </div>
            </div>

        </div>
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