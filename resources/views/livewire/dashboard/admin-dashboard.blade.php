<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <div class="mx-auto max-w-7xl space-y-6">

        <x-mac-panel :overflow="true">
            <x-page-header
                title="Dashboard quản trị giáo lý"
                :description="$currentSchoolYear ? ($todayLabel . ' · Năm học ' . $currentSchoolYear->name . ($semesterLabel ? ' · ' . $semesterLabel : '')) : $todayLabel"
                iconType="default"
                :statValue="$currentSchoolYear ? number_format($stats['students']) : null"
                statLabel="Học sinh"
            >
                <x-slot name="actions">
                    <button
                        wire:click="refresh"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold
                               text-slate-600 bg-slate-100 hover:bg-slate-200
                               rounded-xl transition-all duration-200 disabled:opacity-50">
                        <svg wire:loading.class="animate-spin" class="w-4 h-4"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Làm mới
                    </button>
                </x-slot>
            </x-page-header>

            <div class="px-6 py-4">
                <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                    <span>Xin chào, <span class="font-semibold text-slate-800">{{ auth()->user()->name ?? 'Quản trị viên' }}</span></span>
                </div>
            </div>
        </x-mac-panel>

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
        @if(!$currentSchoolYear)

        <x-stats.page-empty
            title="Chưa có năm học nào được kích hoạt"
            description="Tạo năm học mới, rồi sao chép cấu trúc lớp từ năm cũ nếu cần. Xem trang Cấu hình năm học mới nếu cần hướng dẫn từng bước."
            tone="primary"
            :icon="'<path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z&quot; />'"
        >
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('school-years.index') }}"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-primary-500 hover:bg-primary-600
                          text-white text-sm font-semibold rounded-xl transition-all duration-200 shadow-mac-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Thiết lập năm học
                </a>
                <a href="{{ route('school-years.guide') }}"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-white/80 hover:bg-white
                          text-slate-700 text-sm font-semibold rounded-xl border border-black/[0.06]
                          transition-all duration-200 shadow-mac-sm">
                    Cấu hình năm học mới
                </a>
            </div>
        </x-stats.page-empty>

        @else

        {{-- ============================================================ --}}
        {{-- SECTION 1: SỐ LIỆU NHANH                                    --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stats.stat-card label="Học sinh" :value="number_format($stats['students'])" subline="Đang theo học" />
            <x-stats.stat-card label="Lớp học" :value="number_format($stats['classes'])" subline="Đang hoạt động" />
            <x-stats.stat-card label="Giáo lý viên" :value="number_format($stats['teachers'])" subline="Đang phân công" />
            <x-stats.stat-card
                label="Điểm danh tuần"
                :value="$attendanceWeek['rate'] !== null ? $attendanceWeek['rate'] : null"
                suffix="%"
                :subline="$attendanceWeek['total'] > 0 ? ('Có mặt ' . number_format($attendanceWeek['present']) . '/' . number_format($attendanceWeek['total'])) : 'Chưa có dữ liệu'"
                :valueClass="$attendanceWeek['rate'] !== null ? 'text-slate-900' : 'text-slate-300'"
            />
        </div>

        {{-- ============================================================ --}}
        {{-- SECTION 2: VIỆC CẦN LÀM                                     --}}
        {{-- ============================================================ --}}
        @if(count($todos) > 0)
        <x-mac-panel :overflow="true">
            <div class="px-6 py-4 mac-hairline-b flex items-center gap-3">
                <h2 class="text-base font-semibold text-slate-900">Việc cần làm</h2>
                <span class="inline-flex items-center justify-center w-5 h-5 text-xs
                             font-bold bg-red-500 text-white rounded-full">
                    {{ $todoCount }}
                </span>
            </div>

            <div class="divide-y divide-black/[0.04]">
                @foreach($todos as $todo)
                <div class="flex items-center justify-between px-6 py-4 hover:bg-black/[0.03] transition-colors duration-200">
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
        </x-mac-panel>

        @else
        <x-mac-panel class="px-6 py-4 flex items-center gap-3">
            <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-700">
                Mọi thứ đang ổn — không có việc cần xử lý ngay
            </p>
        </x-mac-panel>
        @endif

        {{-- ============================================================ --}}
        {{-- SECTION 3: ĐIỂM DANH HÔM NAY + HỌC SINH THEO KHỐI          --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- Điểm danh hôm nay --}}
            <x-stats.chart-card title="Điểm danh hôm nay" right="Top 10 lớp">
                @if(count($todayAttendance) > 0)
                    <div class="-mx-6 -my-6 divide-y divide-slate-100">
                        @foreach($todayAttendance as $item)
                            <div class="flex items-center justify-between px-6 py-3 hover:bg-slate-50 transition-colors duration-200">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $item['has_attendance'] ? 'bg-primary-500' : 'bg-slate-300' }}"></span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $item['name'] }}</p>
                                        <p class="text-xs text-slate-400 truncate">{{ $item['block'] }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    @if($item['has_attendance'])
                                        <span class="text-sm font-bold text-slate-900">
                                            {{ $item['attended'] }}/{{ $item['students_count'] }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">Chưa điểm danh</span>
                                    @endif
                                    <a href="{{ $item['url'] }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition-colors duration-200">
                                        Xem →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-4">
                        <a href="{{ route('classes.index') }}" class="text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors duration-200">
                            Xem tất cả lớp →
                        </a>
                    </div>
                @else
                    <div class="text-center py-10">
                        <p class="text-sm text-slate-400">Chưa có dữ liệu</p>
                    </div>
                @endif
            </x-stats.chart-card>

            {{-- Học sinh theo khối --}}
            <x-stats.chart-card
                title="Học sinh theo khối"
                :right="'Nam ' . number_format($genderStats['male']) . ' · Nữ ' . number_format($genderStats['female'])"
            >
                @if(count($studentsByGrade) > 0)
                    @php $maxCount = collect($studentsByGrade)->max('count') ?: 1; @endphp
                    <div class="space-y-4">
                        @foreach($studentsByGrade as $item)
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-semibold text-slate-700">{{ $item['grade'] }}</span>
                                    <span class="text-sm font-bold text-slate-900">{{ number_format($item['count']) }}</span>
                                </div>
                                <progress
                                    value="{{ $item['count'] }}"
                                    max="{{ $maxCount }}"
                                    class="w-full h-1.5 rounded-full overflow-hidden [&::-webkit-progress-bar]:bg-slate-100 [&::-webkit-progress-value]:bg-primary-500 [&::-moz-progress-bar]:bg-primary-500"
                                ></progress>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10">
                        <p class="text-sm text-slate-400">Chưa có dữ liệu</p>
                    </div>
                @endif
            </x-stats.chart-card>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <x-stats.chart-card title="Điểm danh trong tuần" :right="$attendanceWeek['rate'] !== null ? ($attendanceWeek['rate'] . '%') : '—'">
                @if(count($attendanceWeek['days']) > 0)
                    @php $maxRate = 100; @endphp
                    <div class="space-y-3">
                        @foreach($attendanceWeek['days'] as $d)
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-sm font-semibold text-slate-700">{{ $d['date'] }}</span>
                                    <span class="text-xs font-semibold text-slate-500">
                                        @if($d['rate'] !== null)
                                            {{ $d['rate'] }}% · {{ $d['present'] }}/{{ $d['total'] }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                                <progress
                                    value="{{ $d['rate'] ?? 0 }}"
                                    max="{{ $maxRate }}"
                                    class="w-full h-1.5 rounded-full overflow-hidden [&::-webkit-progress-bar]:bg-slate-100 [&::-webkit-progress-value]:bg-amber-500 [&::-moz-progress-bar]:bg-amber-500"
                                ></progress>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10">
                        <p class="text-sm text-slate-400">Chưa có buổi điểm danh trong tuần</p>
                    </div>
                @endif
            </x-stats.chart-card>

            <x-stats.chart-card title="Buổi điểm danh gần đây" right="Mới nhất">
                @if(count($recentAttendanceSessions) > 0)
                    <div class="-mx-6 -my-6 divide-y divide-slate-100">
                        @foreach($recentAttendanceSessions as $s)
                            <div class="px-6 py-3 hover:bg-slate-50 transition-colors duration-200">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $s['class_name'] }}</p>
                                        <p class="text-xs text-slate-400 truncate">{{ $s['date'] }} · {{ $s['type'] }} · {{ $s['status'] }}</p>
                                    </div>
                                    <span class="text-xs font-semibold px-2 py-1 rounded-lg bg-slate-100 text-slate-600 shrink-0">
                                        #{{ $s['id'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10">
                        <p class="text-sm text-slate-400">Chưa có buổi điểm danh</p>
                    </div>
                @endif
            </x-stats.chart-card>
        </div>

        {{-- ============================================================ --}}
        {{-- SECTION 4: TRUY CẬP NHANH                                   --}}
        {{-- ============================================================ --}}
        <x-mac-panel :overflow="true">
            <div class="px-6 py-4 mac-hairline-b">
                <h2 class="text-base font-semibold text-slate-900">Truy cập nhanh</h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-px bg-black/[0.04]">

                {{-- Quản lý lớp --}}
                <a href="{{ route('classes.index') }}"
                    class="flex flex-col items-center justify-center gap-2.5 py-7 bg-white/75
                          hover:bg-black/[0.03] transition-all duration-200 group">
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
                    class="flex flex-col items-center justify-center gap-2.5 py-7 bg-white/75
                          hover:bg-black/[0.03] transition-all duration-200 group">
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
                    class="flex flex-col items-center justify-center gap-2.5 py-7 bg-white/75
                          hover:bg-black/[0.03] transition-all duration-200 group">
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
                    class="flex flex-col items-center justify-center gap-2.5 py-7 bg-white/75
                          hover:bg-black/[0.03] transition-all duration-200 group">
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
        </x-mac-panel>

        @endif {{-- end @if($currentSchoolYear) --}}

    </div>
</div>