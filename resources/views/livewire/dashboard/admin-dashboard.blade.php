<div class="min-h-screen bg-slate-50 p-6">
    <div class="mx-auto max-w-7xl space-y-6">

        {{-- ============================================================ --}}
        {{-- HEADER                                                        --}}
        {{-- ============================================================ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-6 py-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">
                        Xin chào, {{ auth()->user()->name ?? 'Quản trị viên' }} 👋
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        {{ $todayLabel }}
                        @if($activeSchoolYear)
                        &nbsp;·&nbsp;
                        <span class="font-medium text-slate-700">Năm học {{ $activeSchoolYear->name }}</span>
                        @if($semesterLabel)
                        &nbsp;·&nbsp;
                        <span class="font-medium text-primary-600">{{ $semesterLabel }}</span>
                        @endif
                        @endif
                    </p>
                </div>

                <button
                    wire:click="refresh"
                    wire:loading.attr="disabled"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium
                           text-slate-600 bg-slate-100 hover:bg-slate-200
                           rounded-xl transition-all duration-200 disabled:opacity-50">
                    <svg wire:loading.class="animate-spin" class="w-4 h-4"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Làm mới
                </button>
            </div>
        </div>

        {{-- Toast --}}
        @if(session()->has('message'))
        <x-toast-notification type="success" :duration="3000">{{ session('message') }}</x-toast-notification>
        @endif
        @if(session()->has('error'))
        <x-toast-notification type="error" :duration="4000">{{ session('error') }}</x-toast-notification>
        @endif

        {{-- ============================================================ --}}
        {{-- TRƯỜNG HỢP: Chưa có năm học active                           --}}
        {{-- ============================================================ --}}
        @if(!$activeSchoolYear)

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-10 text-center">
            <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h2 class="text-base font-semibold text-slate-900">Chưa có năm học nào được kích hoạt</h2>
            <p class="text-sm text-slate-500 mt-1 mb-5">Vui lòng thiết lập năm học để bắt đầu sử dụng hệ thống</p>
            <a href="{{ route('school-years.index') }}"
                class="inline-flex items-center gap-2 px-5 py-2 bg-primary-500 hover:bg-primary-600
                      text-white text-sm font-medium rounded-xl transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Thiết lập năm học
            </a>
        </div>

        @else

        {{-- ============================================================ --}}
        {{-- SECTION 1: SỐ LIỆU NHANH                                    --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Học sinh --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5
                        hover:shadow-md transition-all duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Học sinh</p>
                        <p class="text-3xl font-bold text-slate-900 mt-2">
                            {{ number_format($stats['students']) }}
                        </p>
                        <p class="text-xs text-slate-400 mt-2">Đang theo học</p>
                    </div>
                    <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Lớp học --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5
                        hover:shadow-md transition-all duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Lớp học</p>
                        <p class="text-3xl font-bold text-slate-900 mt-2">
                            {{ number_format($stats['classes']) }}
                        </p>
                        <p class="text-xs text-slate-400 mt-2">Đang hoạt động</p>
                    </div>
                    <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Giáo lý viên --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5
                        hover:shadow-md transition-all duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Giáo lý viên</p>
                        <p class="text-3xl font-bold text-slate-900 mt-2">
                            {{ number_format($stats['teachers']) }}
                        </p>
                        <p class="text-xs text-slate-400 mt-2">Đang phân công</p>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Điểm danh --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5
                        hover:shadow-md transition-all duration-200">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Điểm danh</p>
                        <p class="text-3xl font-bold text-slate-900 mt-2">
                            @if($stats['attendance'] !== null)
                            {{ $stats['attendance'] }}%
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </p>
                        <p class="text-xs text-slate-400 mt-2">Tuần này</p>
                    </div>
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        {{-- ============================================================ --}}
        {{-- SECTION 2: VIỆC CẦN LÀM                                     --}}
        {{-- ============================================================ --}}
        @if(count($todos) > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
                <h2 class="text-base font-semibold text-slate-900">Việc cần làm</h2>
                <span class="inline-flex items-center justify-center w-5 h-5 text-xs
                             font-bold bg-red-500 text-white rounded-full">
                    {{ $todoCount }}
                </span>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($todos as $todo)
                <div class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors duration-200">
                    <div class="flex items-center gap-3">
                        @if($todo['type'] === 'warning')
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        @else
                        <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        @endif
                        <span class="text-sm font-medium text-slate-800">{{ $todo['message'] }}</span>
                    </div>

                    <a href="{{ route($todo['route']) }}"
                        class="text-xs font-semibold text-primary-600 hover:text-primary-700
                              px-3 py-1.5 bg-primary-50 hover:bg-primary-100
                              rounded-xl transition-all duration-200 whitespace-nowrap">
                        Xem ngay →
                    </a>
                </div>
                @endforeach
            </div>
        </div>

        @else
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-6 py-4
                    flex items-center gap-3">
            <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-700">
                Mọi thứ đang ổn — không có việc cần xử lý ngay
            </p>
        </div>
        @endif

        {{-- ============================================================ --}}
        {{-- SECTION 3: ĐIỂM DANH HÔM NAY + HỌC SINH THEO KHỐI          --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- Điểm danh hôm nay --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-base font-semibold text-slate-900">Điểm danh hôm nay</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Danh sách lớp học đang hoạt động</p>
                </div>

                @if(count($todayAttendance) > 0)
                <div class="divide-y divide-slate-100">
                    @foreach($todayAttendance as $item)
                    <div class="flex items-center justify-between px-6 py-3
                                hover:bg-slate-50 transition-colors duration-200">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full flex-shrink-0
                                {{ $item['has_attendance'] ? 'bg-primary-500' : 'bg-slate-300' }}">
                            </span>
                            <div>
                                <p class="text-sm font-medium text-slate-800">{{ $item['name'] }}</p>
                                <p class="text-xs text-slate-400">{{ $item['block'] }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            @if($item['has_attendance'])
                            <span class="text-sm font-semibold text-slate-900">
                                {{ $item['attended'] }}/{{ $item['students_count'] }}
                            </span>
                            @else
                            <span class="text-xs text-slate-400">Chưa điểm danh</span>
                            @endif
                            <a href="{{ $item['url'] }}"
                                class="text-xs font-semibold text-primary-600 hover:text-primary-700
                                      transition-colors duration-200">
                                Xem →
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if(count($todayAttendance) >= 10)
                <div class="px-6 py-3 border-t border-slate-100 text-center">
                    <a href="{{ route('classes.index') }}"
                        class="text-sm font-semibold text-primary-600 hover:text-primary-700
                              transition-colors duration-200">
                        Xem tất cả lớp →
                    </a>
                </div>
                @endif

                @else
                <div class="px-6 py-12 text-center">
                    <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <p class="text-sm text-slate-400">Chưa có lớp nào</p>
                </div>
                @endif
            </div>

            {{-- Học sinh theo khối --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="text-base font-semibold text-slate-900">Học sinh theo khối</h2>
                    <div class="flex items-center gap-4 mt-1.5">
                        <span class="text-xs text-slate-500 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span>
                            Nam: <strong class="text-slate-700">{{ number_format($genderStats['male']) }}</strong>
                        </span>
                        <span class="text-xs text-slate-500 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-pink-400 inline-block"></span>
                            Nữ: <strong class="text-slate-700">{{ number_format($genderStats['female']) }}</strong>
                        </span>
                    </div>
                </div>

                @if(count($studentsByGrade) > 0)
                @php $maxCount = collect($studentsByGrade)->max('count') ?: 1; @endphp
                <div class="px-6 py-5 space-y-4">
                    @foreach($studentsByGrade as $item)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-slate-700">{{ $item['grade'] }}</span>
                            <span class="text-sm font-bold text-slate-900">{{ number_format($item['count']) }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5">
                            <div class="bg-primary-500 h-1.5 rounded-full transition-all duration-700"
                                style="width: {{ $maxCount > 0 ? ($item['count'] / $maxCount * 100) : 0 }}%">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @else
                <div class="px-6 py-12 text-center">
                    <p class="text-sm text-slate-400">Chưa có dữ liệu</p>
                </div>
                @endif
            </div>

        </div>

        {{-- ============================================================ --}}
        {{-- SECTION 4: TRUY CẬP NHANH                                   --}}
        {{-- ============================================================ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-base font-semibold text-slate-900">Truy cập nhanh</h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-px bg-slate-100">

                {{-- Quản lý lớp --}}
                <a href="{{ route('classes.index') }}"
                    class="flex flex-col items-center justify-center gap-2.5 py-7 bg-white
                          hover:bg-slate-50 transition-all duration-200 group">
                    <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center
                                group-hover:bg-primary-200 transition-colors duration-200">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors duration-200">
                        Quản lý lớp
                    </span>
                </a>

                {{-- Học sinh --}}
                <a href="{{ route('students.index') }}"
                    class="flex flex-col items-center justify-center gap-2.5 py-7 bg-white
                          hover:bg-slate-50 transition-all duration-200 group">
                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center
                                group-hover:bg-green-200 transition-colors duration-200">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors duration-200">
                        Học sinh
                    </span>
                </a>

                {{-- Giáo lý viên --}}
                <a href="{{ route('catechists.index') }}"
                    class="flex flex-col items-center justify-center gap-2.5 py-7 bg-white
                          hover:bg-slate-50 transition-all duration-200 group">
                    <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center
                                group-hover:bg-purple-200 transition-colors duration-200">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors duration-200">
                        Giáo lý viên
                    </span>
                </a>

                {{-- Năm học --}}
                <a href="{{ route('school-years.index') }}"
                    class="flex flex-col items-center justify-center gap-2.5 py-7 bg-white
                          hover:bg-slate-50 transition-all duration-200 group">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center
                                group-hover:bg-amber-200 transition-colors duration-200">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-600 group-hover:text-slate-900 transition-colors duration-200">
                        Năm học
                    </span>
                </a>

            </div>
        </div>

        @endif {{-- end @if($activeSchoolYear) --}}

    </div>
</div>