<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#lop-detail-main" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>
    <div id="lop-detail-main" class="mx-auto max-w-7xl space-y-5">

        {{-- ✅ BREADCRUMB ADDED --}}
        <x-breadcrumb :items="[
            [
                'label' => 'Trang chủ',
                'url' => route('ds-lop')
            ],
            [
                'label' => 'Quản lý lớp học',
                'url' => route('ds-lop'),
                'icon' => '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z\'/></svg>'
            ],
            [
                'label' => $lopData['name'],
            ]
        ]" separator="arrow" />

        {{-- Toast Notifications (live region + standardized durations) --}}
        <div role="status" aria-live="polite" aria-atomic="true">
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
        </div>

        {{-- Inline class selector to switch class without leaving page --}}
        <div class="p-3">
            @livewire('class-filter-selector', [
            'parish_id' => $parishId,
            'showNamHoc' => true,
            'showKhoi' => true,
            'showLop' => true,
            'selectedNamHoc' => $namHoc->id ?? null,
            'selectedKhoi' => $block->id ?? null,
            'selectedLop' => $lopData['id'] ?? null,
            ])
        </div>

        {{-- Class Info Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Header --}}
            <x-page-header
                :title="$lopData['name']"
                :description="$lopData['symbol'] ? 'Mã lớp: ' . $lopData['symbol'] : ''"
                icon="class"
                gradient="purple"
                :count="$statistics['total']"
                count-label="Tổng sĩ số" />

            {{-- Compact Stats - Inline --}}
            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-slate-600">Năm học:</span>
                        <span class="font-semibold text-slate-900">{{ $namHoc->name ?? 'N/A' }}</span>
                    </div>
                    <span class="text-slate-300">•</span>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span class="text-slate-600">Khối:</span>
                        <span class="font-semibold text-slate-900">{{ $block->name ?? 'N/A' }}</span>
                    </div>
                    <span class="text-slate-300">•</span>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-slate-600">Nam:</span>
                        <span class="font-semibold text-blue-600">{{ $statistics['male'] }}</span>
                    </div>
                    <span class="text-slate-300">•</span>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="text-slate-600">Nữ:</span>
                        <span class="font-semibold text-pink-600">{{ $statistics['female'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Teachers Section - Compact --}}

            @if($teachers && $teachers->count() > 0)
            <div class="p-4 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Giáo lý viên
                    <span class="text-xs font-normal text-slate-600">({{ $teachers->count() }})</span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($teachers as $teacher)
                    <x-teacher.badge :name="$teacher['name']" :isChuNhiem="$teacher['is_chu_nhiem']" />
                    @endforeach
                </div>
            </div>
            @else
            <div class="p-4 border-b border-slate-200">
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="text-sm text-amber-900 font-medium">Chưa phân công giáo lý viên</p>
                </div>
            </div>
            @endif

            {{-- Schedule Section - Compact --}}
            @if($lopData['start_date_one'] || $lopData['end_date_one'] || $lopData['start_date_two'] || $lopData['end_date_two'])
            <div class="p-4 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Lịch học
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @if($lopData['start_date_one'] && $lopData['end_date_one'])
                    <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg border border-slate-200">
                        <span class="text-xs font-semibold text-slate-700">Học kỳ 1</span>
                        <span class="text-xs text-slate-600 font-medium">
                            {{ \Carbon\Carbon::parse($lopData['start_date_one'])->format('d/m/Y') }} -
                            {{ \Carbon\Carbon::parse($lopData['end_date_one'])->format('d/m/Y') }}
                        </span>
                    </div>
                    @endif

                    @if($lopData['start_date_two'] && $lopData['end_date_two'])
                    <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg border border-slate-200">
                        <span class="text-xs font-semibold text-slate-700">Học kỳ 2</span>
                        <span class="text-xs text-slate-600 font-medium">
                            {{ \Carbon\Carbon::parse($lopData['start_date_two'])->format('d/m/Y') }} -
                            {{ \Carbon\Carbon::parse($lopData['end_date_two'])->format('d/m/Y') }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Thay thế schedule section --}}
            @if($lopData['start_date_one'] || $lopData['end_date_one'] || $lopData['start_date_two'] || $lopData['end_date_two'])
            <div class="p-4 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Lịch học
                </h3>
                <div class="space-y-2">
                    @if($lopData['start_date_one'] && $lopData['end_date_one'])
                    <div class="flex items-center gap-3 p-3 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl border border-indigo-100">
                        <div class="flex-shrink-0 w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">HK1</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-700">Học kỳ 1</p>
                            <p class="text-xs text-slate-600 font-medium">
                                {{ \Carbon\Carbon::parse($lopData['start_date_one'])->format('d/m/Y') }}
                                -
                                {{ \Carbon\Carbon::parse($lopData['end_date_one'])->format('d/m/Y') }}
                            </p>
                        </div>
                        @if(\Carbon\Carbon::now()->between($lopData['start_date_one'], $lopData['end_date_one']))
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-md text-xs font-semibold">
                            Đang học
                        </span>
                        @endif
                    </div>
                    @endif

                    @if($lopData['start_date_two'] && $lopData['end_date_two'])
                    <div class="flex items-center gap-3 p-3 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl border border-purple-100">
                        <div class="flex-shrink-0 w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">HK2</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-700">Học kỳ 2</p>
                            <p class="text-xs text-slate-600 font-medium">
                                {{ \Carbon\Carbon::parse($lopData['start_date_two'])->format('d/m/Y') }}
                                -
                                {{ \Carbon\Carbon::parse($lopData['end_date_two'])->format('d/m/Y') }}
                            </p>
                        </div>
                        @if(\Carbon\Carbon::now()->between($lopData['start_date_two'], $lopData['end_date_two']))
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-md text-xs font-semibold">
                            Đang học
                        </span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Note Section - Compact --}}
            @if($lopData['note'])
            <div class="p-4 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                    Ghi chú
                </h3>
                <p class="text-slate-700 text-sm leading-relaxed">{{ $lopData['note'] }}</p>
            </div>
            @endif

            <div class="p-4 bg-white space-y-2">
                {{-- Primary Action --}}
                <a href="{{ $slugUrl }}"
                    class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 active:scale-[0.98] transition-all shadow-sm font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Xem danh sách học sinh
                </a>

                {{-- Secondary Actions Grid --}}
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('attendance', $lopData['id']) }}"
                        class="flex items-center justify-center gap-2 px-3 py-2.5 bg-slate-100 text-slate-900 rounded-xl hover:bg-slate-200 active:scale-[0.98] transition-all font-medium text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span>Điểm danh</span>
                    </a>

                    <a href="{{ route('lop.edit', $lopData['id']) }}"
                        class="flex items-center justify-center gap-2 px-3 py-2.5 bg-slate-100 text-slate-900 rounded-xl hover:bg-slate-200 active:scale-[0.98] transition-all font-medium text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Chỉnh sửa</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush