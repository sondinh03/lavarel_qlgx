<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-6">
    <a href="#lop-detail-main" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>
    <div id="lop-detail-main" class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            [
                'label' => 'Trang chủ',
                'url' => route('classes.index')
            ],
            [
                'label' => 'Quản lý lớp học',
                'url' => route('classes.index'),
                'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z\'/></svg>'
            ],
            [
                'label' => $lopData['name'] ?? 'Chi tiết lớp',
            ]
        ]" separator="arrow" />

        {{-- Toast Notifications --}}
        @if (session()->has('message'))
        <x-toast-notification type="success" :duration="3500">
            {{ session('message') }}
        </x-toast-notification>
        @endif

        @if (session()->has('error'))
        <x-toast-notification type="error" :duration="3500">
            {{ session('error') }}
        </x-toast-notification>
        @endif

        {{-- Class Filter Selector --}}
        @livewire('class-filter-selector', [
            'parish-id' => $parishId,
            'showNamHoc' => true,
            'showKhoi' => true,
            'showLop' => true,
            'selectedNamHoc' => $namHoc->id ?? null,
            'selectedKhoi' => $block->id ?? null,
            'selectedLop' => $lopData['id'] ?? null,
        ])

        {{-- Desktop Grid Layout - 2 cột --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- LEFT COLUMN: Class Info (2/3 width) --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Main Class Info Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    {{-- Header với stats inline --}}
                    <div class="p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-primary-500 rounded-xl flex items-center justify-center shadow-sm">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <div>
                                    <h1 class="text-2xl font-bold text-slate-900">{{ $lopData['name'] ?? 'N/A' }}</h1>
                                    @if(!empty($lopData['symbol']))
                                    <p class="text-sm text-slate-600 mt-1">
                                        Mã lớp: <span class="font-mono font-semibold text-slate-900">{{ $lopData['symbol'] }}</span>
                                    </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Badge tổng sĩ số --}}
                            <div class="text-right">
                                <div class="text-3xl font-bold text-primary-600">{{ $statistics['total'] ?? 0 }}</div>
                                <div class="text-xs text-slate-600 font-medium">Tổng sĩ số</div>
                            </div>
                        </div>
                    </div>

                    {{-- Stats Grid - 4 cột --}}
                    <div class="grid grid-cols-4 gap-4 p-6 bg-slate-50 border-b border-slate-200">
                        {{-- Năm học --}}
                        <div class="flex flex-col items-center text-center p-4 bg-white rounded-xl border border-slate-200">
                            <svg class="w-5 h-5 text-primary-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-xs text-slate-600 mb-1">Năm học</span>
                            <span class="text-sm font-semibold text-slate-900">
                                {{ $namHoc->name ?? 'N/A' }}
                            </span>
                        </div>

                        {{-- Khối --}}
                        <div class="flex flex-col items-center text-center p-4 bg-white rounded-xl border border-slate-200">
                            <svg class="w-5 h-5 text-primary-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span class="text-xs text-slate-600 mb-1">Khối</span>
                            <span class="text-sm font-semibold text-slate-900">
                                {{ $block->name ?? 'N/A' }}
                            </span>
                        </div>

                        {{-- Nam --}}
                        <div class="flex flex-col items-center text-center p-4 bg-white rounded-xl border border-slate-200">
                            <svg class="w-5 h-5 text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-xs text-slate-600 mb-1">Nam</span>
                            <span class="text-lg font-bold text-slate-900">
                                {{ $statistics['male'] ?? 0 }}
                            </span>
                        </div>

                        {{-- Nữ --}}
                        <div class="flex flex-col items-center text-center p-4 bg-white rounded-xl border border-slate-200">
                            <svg class="w-5 h-5 text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-xs text-slate-600 mb-1">Nữ</span>
                            <span class="text-lg font-bold text-slate-900">
                                {{ $statistics['female'] ?? 0 }}
                            </span>
                        </div>
                    </div>

                    {{-- Ghi chú (nếu có) --}}
                    @if(!empty($lopData['note']))
                    <div class="p-6">
                        <h3 class="text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                            Ghi chú
                        </h3>
                        <p class="text-slate-700 text-sm leading-relaxed bg-slate-50 p-3 rounded-lg">{{ $lopData['note'] }}</p>
                    </div>
                    @endif
                </div>

                {{-- Schedule Card --}}
                @if(!empty($lopData['start_date_one']) || !empty($lopData['start_date_two']))
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Lịch học
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            {{-- HK1 --}}
                            @if(!empty($lopData['start_date_one']) && !empty($lopData['end_date_one']))
                            <div class="flex items-center gap-4 p-4 bg-gradient-to-br from-primary-50 to-primary-50 rounded-xl border border-primary-200">
                                <div class="flex-shrink-0 w-12 h-12 bg-primary-500 rounded-xl flex items-center justify-center shadow-sm">
                                    <span class="text-white font-bold">HK1</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-900">Học kỳ 1</p>
                                    <p class="text-xs text-slate-600 font-medium mt-0.5">
                                        {{ $lopData['start_date_one'] }} - {{ $lopData['end_date_one'] }}
                                    </p>
                                </div>
                            </div>
                            @endif

                            {{-- HK2 --}}
                            @if(!empty($lopData['start_date_two']) && !empty($lopData['end_date_two']))
                            <div class="flex items-center gap-4 p-4 bg-gradient-to-br from-primary-50 to-primary-100 rounded-xl border border-primary-200">
                                <div class="flex-shrink-0 w-12 h-12 bg-primary-500 rounded-xl flex items-center justify-center shadow-sm">
                                    <span class="text-white font-bold">HK2</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-slate-900">Học kỳ 2</p>
                                    <p class="text-xs text-slate-600 font-medium mt-0.5">
                                        {{ $lopData['start_date_two'] }} - {{ $lopData['end_date_two'] }}
                                    </p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- RIGHT COLUMN: Sidebar (1/3 width) --}}
            <div class="space-y-5">

                {{-- Quick Actions Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 bg-slate-50">
                        <h3 class="text-sm font-bold text-slate-900">Thao tác nhanh</h3>
                    </div>
                    <div class="p-4 space-y-2">
                        {{-- Primary Action: Danh sách học sinh --}}
                        <a href="{{ route('classes.show', $lopData['id'] ?? 0) }}"
                            class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl hover:from-primary-600 hover:to-primary-700 active:scale-[0.98] transition-all shadow-sm font-semibold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Danh sách học sinh
                        </a>

                        {{-- Secondary Actions --}}
                        <a href="{{ route('attendance', $lopData['id'] ?? 0) }}"
                            class="flex items-center gap-3 w-full px-4 py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-900 rounded-xl transition-all font-medium">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span>Điểm danh</span>
                        </a>

                        <a href="{{ route('dashboard', $lopData['id'] ?? 0) }}"
                            class="flex items-center gap-3 w-full px-4 py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-900 rounded-xl transition-all font-medium">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span>Kết quả học tập</span>
                        </a>

                        <a href="{{ route('classes.edit', $lopData['id'] ?? 0) }}"
                            class="flex items-center gap-3 w-full px-4 py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-900 rounded-xl transition-all font-medium">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Chỉnh sửa lớp</span>
                        </a>
                    </div>
                </div>

                {{-- Teachers Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-4 border-b border-slate-200 bg-slate-50">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Giáo lý viên
                            @if(!empty($teachers) && count($teachers) > 0)
                            <span class="text-xs font-normal text-slate-600">({{ count($teachers) }})</span>
                            @endif
                        </h3>
                    </div>
                    <div class="p-4">
                        @if(!empty($teachers) && count($teachers) > 0)
                        <div class="space-y-2">
                            @foreach($teachers as $teacher)
                            <x-teacher.badge 
                                :name="$teacher['name']" 
                                :isChuNhiem="$teacher['is_chu_nhiem'] ?? false" 
                                size="8" />
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-6">
                            <svg class="w-12 h-12 text-amber-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="text-sm text-slate-600 font-medium mb-3">Chưa phân công giáo lý viên</p>
                            <button class="text-xs text-primary-600 hover:text-primary-700 font-semibold">
                                + Phân công ngay
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush