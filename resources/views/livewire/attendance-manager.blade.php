<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    @php
    $selectedClassName = $this->selectedClassName;
    $hasUnsavedChanges = $this->hasUnsavedChanges;
    $pendingCount = $this->pendingCount;
    @endphp



    {{-- Loading Indicator --}}
    <div
        wire:loading
        wire:loading.delay.shortest
        class="fixed top-0 left-0 right-0 z-[9999] pointer-events-none">

        {{-- Progress bar indeterminate --}}
        <div class="h-0.5 bg-primary-100 overflow-hidden">
            <div class="h-full bg-primary-500 animate-[indeterminate_1.4s_ease-in-out_infinite]"></div>
        </div>

        {{-- Pill --}}
        <div class="absolute top-3 right-4 flex items-center gap-1.5
                bg-white/90 backdrop-blur-sm shadow-md
                rounded-full px-3 py-1 text-xs font-medium text-slate-600
                border border-slate-200">
            <svg class="animate-spin w-3 h-3 text-primary-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            <span>Đang xử lý</span>
        </div>
    </div>

    <div class="mx-auto max-w-7xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb
            :items="[
                ['label' => 'Trang chủ', 'url' => route('dashboard')],
                ['label' => 'Điểm danh', 'url' => route('attendance.show')],
                ['label' => $selectedClassName]
            ]"
            separator="arrow" />

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

            @if (session()->has('warning'))
            <x-toast-notification type="warning" :duration="4000">
                {{ session('warning') }}
            </x-toast-notification>
            @endif

            @if (session()->has('info'))
            <x-toast-notification type="info" :duration="3500">
                {{ session('info') }}
            </x-toast-notification>
            @endif
        </div>

        {{-- Main Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            {{-- Header --}}
            <x-page-header
                title="Điểm danh{{ $selectedClassId ? ' - ' . $selectedClassName : '' }}"
                description="Điểm danh {{ $attendanceType == 1 ? 'đi học' : 'đi lễ' }}{{ $selectedClassId ? ' • ' . $students->count() . ' học sinh • ' . count($sessions) . ' buổi' : '' }}"
                :stat-value="$students->count()"
                stat-label="Học sinh"
                icon-type="attendance">
            </x-page-header>

            {{-- Actions Bar --}}
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                    {{-- LEFT: Filters --}}
                    <div class="flex flex-col gap-3 w-full lg:flex-row lg:items-center">
                        {{-- Filter Bar --}}
                        <livewire:filters.filter-bar
                            :parish-id="$parishId"
                            :show-nam-hoc="true"
                            :show-khoi="true"
                            :show-lop="true"
                            :show-ky="true"
                            :selected-nam-hoc="$selectedNamHoc"
                            :selected-khoi="$selectedKhoi"
                            :selected-lop="$selectedClassId"
                            :selected-ky="$selectedKy" />

                        {{-- Search --}}
                        <input
                            wire:model.live.debounce.500ms="search"
                            placeholder="Tìm học sinh..."
                            class="w-56 px-3 py-2 rounded-xl
                                border border-slate-300
                                text-sm focus:outline-none
                                focus:ring-2 focus:ring-primary-500" />
                    </div>

                    {{-- RIGHT: Actions --}}
                    <div class="flex items-center gap-3">
                        @if($hasUnsavedChanges)
                        <button
                            wire:click="discardDrafts"
                            class="px-4 py-2 border border-red-300 text-red-700 rounded-xl hover:bg-red-50 transition-colors
                                text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Hủy ({{ $pendingCount }})
                        </button>
                        @endif

                        {{-- Export Button (future) --}}
                        <button
                            class="px-4 py-2 bg-white border border-slate-300 rounded-xl
                                text-sm font-medium text-slate-700
                                hover:bg-slate-50 transition-colors"
                            disabled>
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Xuất Excel
                        </button>

                        {{-- Save Button --}}
                        <x-action-button
                            wire="saveAttendance"
                            icon="save"
                            :loading="true"
                            :disabled="empty($draftAttendance)">
                            Lưu điểm danh
                        </x-action-button>
                    </div>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="bg-primary-50 p-1 flex gap-1 border-b border-slate-200">
                <button
                    wire:click="$set('attendanceType', 1)"
                    class="flex-1 py-2 rounded-lg text-sm font-semibold transition-all
                        {{ $attendanceType == 1 ? 'bg-white text-primary-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Điểm danh đi học
                </button>
                <button
                    wire:click="$set('attendanceType', 2)"
                    class="flex-1 py-2 rounded-lg text-sm font-semibold transition-all
                        {{ $attendanceType == 2 ? 'bg-white text-primary-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Điểm danh đi lễ
                </button>
            </div>
        </div>

        {{-- Quick Stats 
        @if($selectedClassId)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs text-slate-600 font-medium">Tổng học sinh</div>
                        <div class="text-2xl font-bold text-slate-900 mt-1">{{ $students->count() }}
    </div>
</div>
<div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
</div>
</div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-xs text-slate-600 font-medium">Số buổi</div>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ count($sessions) }}</div>
        </div>
        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-xs text-slate-600 font-medium">Chưa lưu</div>
            <div class="text-2xl font-bold text-amber-600 mt-1">{{ $pendingCount }}</div>
        </div>
        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-xs text-slate-600 font-medium">Loại điểm danh</div>
            <div class="text-lg font-bold text-purple-600 mt-1">
                {{ $attendanceType == 1 ? 'Đi học' : 'Đi lễ' }}
            </div>
        </div>
        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
        </div>
    </div>
</div>
</div>
@endif
--}}

@if (!$selectedClassId)
<div class="text-center text-gray-500 py-10">
    Vui lòng chọn lớp để bắt đầu điểm danh
</div>
@elseif ($students->isEmpty())
<div class="text-center text-gray-500 py-10">
    Lớp chưa có học sinh
</div>
@else
{{-- Table Section --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    @if($students->count() > 0 && count($sessions) > 0)
    {{-- Desktop Table --}}
    <div class="hidden lg:block overflow-x-auto">
        <table class="w-full border-separate border-spacing-0">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <x-table-header class="sticky left-0 bg-slate-50 z-20">#</x-table-header>
                    <x-table-header class="sticky left-12 bg-slate-50 z-20 min-w-[200px]">Họ và tên</x-table-header>
                    @foreach($sessions as $session)
                    <x-table-header class="text-center min-w-[120px]">
                        <div class="flex flex-col gap-1">
                            <div class="{{ $session['locked'] ? 'text-slate-400' : '' }} flex items-center justify-center gap-1">
                                <span>{{ $session['dayName'] }}</span>
                                @if($session['locked'])
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                @endif
                            </div>
                            <div class="text-[10px] {{ $session['locked'] ? 'text-slate-400' : 'text-slate-600' }}">
                                {{ $session['fullDate'] }}
                            </div>
                            @if(!$session['locked'])
                            <button
                                wire:click="markAllPresent({{ $session['id'] }})"
                                class="text-[9px] text-green-600 hover:text-green-700 hover:underline"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="markAllPresent">✓ Tất cả</span>
                                <span wire:loading wire:target="markAllPresent">⏳</span>
                            </button>
                            @endif
                        </div>
                    </x-table-header>
                    @endforeach
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @foreach ($students as $index => $student)
                <tr class="hover:bg-slate-50 transition-colors" wire:key="student-{{ $student->id }}">
                    {{-- STT --}}
                    <td class="px-6 py-4 text-sm text-slate-500 sticky left-0 bg-white z-10">
                        {{ $index + 1 }}
                    </td>

                    {{-- Tên học sinh --}}
                    <td class="px-6 py-4 sticky left-12 bg-white z-10">
                        <div class="text-xs text-slate-500">{{ $student->saint_name }}</div>
                        <div class="font-semibold text-slate-900">
                            {{ $student->full_name }}
                        </div>
                    </td>

                    {{-- Attendance cells --}}
                    @foreach($sessions as $session)
                    @php
                    $status = $this->getAttendanceStatus($student->id, $session['dateStr']);
                    $draftKey = $student->id . '_' . $session['id'];
                    $recordKey = $student->id . '_' . $session['dateStr'];
                    $note = $draftAttendance[$draftKey]['note']
                    ?? $attendanceRecords[$recordKey]['note']
                    ?? null;
                    @endphp
                    <td class="px-3 py-3 text-center">
                        @if($session['locked'])
                        <div class="flex items-center justify-center h-8" x-data="{ open: false }">
                            @if($status == 1)
                            <span class="text-green-700 font-medium">✓</span>
                            @elseif($status == 2)
                            <div class="relative inline-block">
                                <button
                                    @mouseenter="open = true"
                                    @mouseleave="open = false"
                                    class="text-yellow-700 font-medium cursor-help flex items-center gap-0.5">
                                    P
                                    @if($note)
                                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                    @endif
                                </button>
                                @if($note)
                                <div x-show="open"
                                    x-transition
                                    x-cloak
                                    class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 w-64
                                                   p-3 bg-slate-900 text-white text-xs rounded-lg shadow-xl z-30">
                                    <div class="font-semibold mb-1">Lý do vắng:</div>
                                    <div class="text-slate-200">{{ $note }}</div>
                                    <div class="absolute left-1/2 -translate-x-1/2 top-full w-0 h-0
                                                        border-l-4 border-r-4 border-t-4
                                                        border-l-transparent border-r-transparent border-t-slate-900"></div>
                                </div>
                                @endif
                            </div>
                            @elseif($status == 3)
                            <span class="text-red-700 font-medium">✕</span>
                            @else
                            <span class="text-xs text-slate-400">-</span>
                            @endif
                        </div>
                        @else
                        <div class="flex gap-1 justify-center">
                            <button
                                wire:click="setAttendance({{ $student->id }}, {{ $session['id'] }}, {{ $status == 1 ? 'null' : 1 }})"
                                class="px-2 py-1 rounded text-xs font-medium transition-all
                                            {{ $status == 1 ? 'bg-green-500 text-white shadow-md scale-105' : 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100' }}">
                                ✓
                            </button>

                            <div class="relative inline-block" x-data="{ open: false }">
                                <button
                                    wire:click="setAttendance({{ $student->id }}, {{ $session['id'] }}, {{ $status == 2 ? 'null' : 2 }})"
                                    @mouseenter="open = true"
                                    @mouseleave="open = false"
                                    @click.stop="open = !open"
                                    class="px-2 py-1 rounded text-xs font-medium transition-all relative
                                                {{ $status == 2 ? 'bg-yellow-400 text-slate-900 shadow-md scale-105' : 'bg-amber-100 text-amber-800 border border-yellow-200 hover:bg-yellow-100' }}">
                                    P
                                    @if($note)
                                    <span class="absolute -top-1 -right-1 w-2 h-2 bg-blue-500 rounded-full ring-2 ring-white"></span>
                                    @endif
                                </button>
                                @if($note && $status == 2)
                                <div x-show="open"
                                    @click.away="open = false"
                                    x-transition
                                    x-cloak
                                    class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 w-48
                                                   p-2 bg-slate-900 text-white text-[10px] rounded-lg shadow-xl z-30">
                                    <div class="font-semibold mb-1">Lý do:</div>
                                    <div class="text-slate-200 line-clamp-2">{{ $note }}</div>
                                    <div class="absolute left-1/2 -translate-x-1/2 top-full w-0 h-0
                                                        border-l-4 border-r-4 border-t-4
                                                        border-l-transparent border-r-transparent border-t-slate-900"></div>
                                </div>
                                @endif
                            </div>

                            <button
                                wire:click="setAttendance({{ $student->id }}, {{ $session['id'] }}, {{ $status == 3 ? 'null' : 3 }})"
                                class="px-2 py-1 rounded text-xs font-medium transition-all
                                            {{ $status == 3 ? 'bg-red-500 text-white shadow-md scale-105' : 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100' }}">
                                ✕
                            </button>
                        </div>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach

                {{-- Stats Row --}}
                <tr class="bg-slate-50 font-semibold border-t-2 border-slate-300">
                    <td colspan="2" class="px-6 py-3 text-sm text-slate-900 sticky left-0 bg-slate-50 z-10">
                        Thống kê
                    </td>
                    @foreach($sessions as $session)
                    @php $stats = $this->getDateStats($session['dateStr']); @endphp
                    <td class="px-3 py-3 text-center">
                        <div class="flex flex-col gap-1 text-xs">
                            <div class="text-green-600">✓ {{ $stats['present'] }}</div>
                            <div class="text-yellow-600">P {{ $stats['absentPermitted'] }}</div>
                            <div class="text-red-600">✕ {{ $stats['absentNotPermitted'] }}</div>
                        </div>
                    </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Mobile View --}}
    <div class="lg:hidden">
        {{-- Date Selector - Sticky --}}
        @php
        $currentSession = collect($sessions)->firstWhere('dateStr', $selectedDate);
        $locked = $currentSession['locked'] ?? false;
        $stats = $this->getDateStats($selectedDate ?? '');
        $total = $students->count();
        @endphp

        <div class="sticky top-0 z-30 bg-white border-b border-slate-200 shadow-sm">

            {{-- Chips scroll --}}
            <div class="flex gap-2 overflow-x-auto px-3 py-3 scrollbar-hide snap-x snap-mandatory">
                @foreach($sessions as $session)
                @php
                $isActive = $session['dateStr'] === $selectedDate;
                $chipStats = $this->getDateStats($session['dateStr']);
                $hasAnyRecord = ($chipStats['present'] + $chipStats['absentPermitted'] + $chipStats['absentNotPermitted']) > 0;
                @endphp
                <button
                    wire:click="$set('selectedDate', '{{ $session['dateStr'] }}')"
                    class="flex-shrink-0 snap-start flex flex-col items-center gap-1 px-3 py-2 rounded-xl border transition-all min-w-[72px]
                {{ $isActive
                    ? 'bg-primary-600 border-primary-600 text-white shadow-md'
                    : 'bg-white border-slate-200 text-slate-600 hover:border-primary-300' }}">

                    {{-- Ngày --}}
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-bold">{{ $session['fullDate'] }}</span>
                        @if($session['locked'])
                        <svg class="w-2.5 h-2.5 {{ $isActive ? 'text-white/70' : 'text-slate-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        @endif
                    </div>

                    {{-- Thứ --}}
                    <span class="text-[10px] {{ $isActive ? 'text-white/80' : 'text-slate-400' }}">
                        {{ $session['dayName'] }}
                    </span>

                    {{-- Dot indicator: đã điểm danh chưa --}}
                    <div class="flex gap-0.5 mt-0.5">
                        @if($hasAnyRecord)
                        <span class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-white/70' : 'bg-primary-400' }}"></span>
                        @else
                        <span class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-white/30' : 'bg-slate-200' }}"></span>
                        @endif
                    </div>
                </button>
                @endforeach
            </div>

            {{-- Active session info bar --}}
            @if($currentSession)
            <div class="flex items-center justify-between px-4 py-2 bg-slate-50 border-t border-slate-100">

                {{-- Stats nhỏ --}}
                <div class="flex items-center gap-3 text-xs">
                    <span class="flex items-center gap-1 text-green-600 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                        {{ $stats['present'] }}
                    </span>
                    <span class="flex items-center gap-1 text-yellow-600 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span>
                        {{ $stats['absentPermitted'] }}
                    </span>
                    <span class="flex items-center gap-1 text-red-600 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                        {{ $stats['absentNotPermitted'] }}
                    </span>
                    <span class="text-slate-400">/ {{ $total }}</span>
                </div>

                {{-- Mark all / Locked badge --}}
                @if($locked)
                <span class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-500
                     bg-slate-200 rounded-full px-2 py-0.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Đã khóa
                </span>
                @else
                <button
                    wire:click="markAllPresent({{ $currentSession['id'] }})"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-green-700
                   bg-green-100 hover:bg-green-200 rounded-full px-3 py-1 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    Tất cả có mặt
                </button>
                @endif
            </div>
            @endif
        </div>

        {{-- Table View --}}
        <div class="overflow-x-auto">
            <table class="w-full border-separate border-spacing-0">
                <thead class="bg-slate-50 border-b-2 border-slate-200">
                    <tr>
                        <th class="w-12 px-2 py-3 text-left text-xs font-bold text-slate-900 uppercase tracking-wider border-r border-slate-200">
                            STT
                        </th>
                        <th class="w-32 px-2 py-3 text-left text-xs font-bold text-slate-900 uppercase tracking-wider border-r border-slate-200">
                            Họ và tên
                        </th>
                        <th class="px-3 py-3 text-center text-xs font-bold text-slate-900 uppercase tracking-wider">
                            <div class="flex flex-col gap-1">
                                <span class="{{ $locked ? 'text-slate-400' : '' }}">Điểm danh</span>
                                @if($locked)
                                <div class="flex items-center justify-center gap-1 text-slate-400">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span class="text-[10px]">Đã khóa</span>
                                </div>
                                @endif
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach ($students as $index => $student)
                    @php $status = $this->getAttendanceStatus($student->id, $selectedDate ?? ''); @endphp
                    <tr class="hover:bg-slate-50 transition-colors" wire:key="mobile-student-{{ $student->id }}">
                        <td class="w-12 px-2 py-3 text-sm text-slate-500 font-medium border-r border-slate-100">
                            {{ $index + 1 }}
                        </td>
                        <td class="w-32 px-2 py-3 border-r border-slate-100">
                            <div class="flex flex-col">
                                <span class="text-[10px] text-slate-500 leading-tight">{{ $student->saint_name }}</span>
                                <span class="font-semibold text-slate-900 text-xs leading-tight">
                                    {{ $student->last_name }} {{ $student->name }}
                                </span>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            @if($locked)
                            <div class="flex items-center justify-center">
                                @if($status == 1)
                                <div class="flex flex-col items-center gap-1">
                                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center shadow-sm">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-[9px] text-green-700 font-medium">Có mặt</span>
                                </div>
                                @elseif($status == 2)
                                <div class="flex flex-col items-center gap-1">
                                    <div class="w-8 h-8 bg-yellow-400 rounded-lg flex items-center justify-center shadow-sm">
                                        <span class="text-slate-900 font-bold text-sm">P</span>
                                    </div>
                                    <span class="text-[9px] text-yellow-700 font-medium">Vắng CP</span>
                                </div>
                                @elseif($status == 3)
                                <div class="flex flex-col items-center gap-1">
                                    <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center shadow-sm">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <span class="text-[9px] text-red-700 font-medium">Vắng KP</span>
                                </div>
                                @else
                                <span class="text-xs text-slate-400">-</span>
                                @endif
                            </div>
                            @else
                            <div class="flex gap-1 justify-center">
                                <button
                                    wire:click="setAttendance({{ $student->id }}, {{ $currentSession['id'] ?? 0 }}, {{ $status == 1 ? 'null' : 1 }})"
                                    class="w-9 h-9 rounded-md flex items-center justify-center transition
                                                {{ $status == 1 ? 'bg-green-500 text-white shadow-sm' : 'bg-green-50 text-green-700 border border-green-100 hover:bg-green-100' }}"
                                    wire:loading.attr="disabled">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </button>
                                <button
                                    wire:click="setAttendance({{ $student->id }}, {{ $currentSession['id'] ?? 0 }}, {{ $status == 2 ? 'null' : 2 }})"
                                    class="w-9 h-9 rounded-md flex items-center justify-center font-semibold transition
                                                {{ $status == 2 ? 'bg-yellow-400 text-slate-900 shadow-sm' : 'bg-yellow-50 text-yellow-700 border border-yellow-100 hover:bg-yellow-100' }}"
                                    wire:loading.attr="disabled">
                                    P
                                </button>
                                <button
                                    wire:click="setAttendance({{ $student->id }}, {{ $currentSession['id'] ?? 0 }}, {{ $status == 3 ? 'null' : 3 }})"
                                    class="w-9 h-9 rounded-md flex items-center justify-center transition
                                                {{ $status == 3 ? 'bg-red-500 text-white shadow-sm' : 'bg-red-50 text-red-700 border border-red-100 hover:bg-red-100' }}"
                                    wire:loading.attr="disabled">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @else
    {{-- Empty State --}}
    <div class="text-center py-12">
        <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
        </svg>
        <p class="mt-4 text-lg text-slate-500">
            @if(empty($sessions))
            Chưa có buổi điểm danh nào
            @else
            Không có dữ liệu để hiển thị
            @endif
        </p>
    </div>
    @endif
</div>
@endif
</div>
</div>

{{-- Note Modal --}}
@if ($showNoteModal)
<div
    class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="note-modal-title"
    wire:click="closeNoteModal">
    <div
        class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col"
        wire:click.stop>

        {{-- Header --}}
        <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-yellow-50 to-white">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h2 id="note-modal-title" class="text-xl font-bold text-slate-900">
                        Vắng có phép
                    </h2>
                    <p class="text-sm text-slate-600 mt-1">Ghi chú lý do vắng mặt</p>
                </div>
                <button wire:click="closeNoteModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-5">
            {{-- Student Info --}}
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs text-slate-500">Học sinh</div>
                        <div class="font-semibold text-slate-900">{{ $currentStudentName }}</div>
                    </div>
                </div>
            </div>

            @error('attendanceNote')
            <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                <p class="text-sm text-red-700">{{ $message }}</p>
            </div>
            @enderror

            {{-- Note Input --}}
            <div>
                <label for="attendance-note" class="block text-sm font-semibold text-slate-700 mb-2">
                    Lý do vắng <span class="text-slate-400 font-normal">(không bắt buộc)</span>
                </label>
                <textarea
                    id="attendance-note"
                    wire:model.defer="attendanceNote"
                    rows="4"
                    placeholder="Vd: Bệnh, về quê, đi học thêm, gia đình có việc..."
                    class="w-full px-4 py-3 rounded-xl border border-slate-300
                           focus:outline-none focus:ring-2 focus:ring-yellow-500
                           resize-none text-sm"
                    maxlength="500"></textarea>
                <div class="mt-1 text-xs text-slate-500 flex items-center justify-between">
                    <span>Tối đa 500 ký tự</span>
                    <span>{{ strlen($attendanceNote) }}/500</span>
                </div>
            </div>

            {{-- Quick Reasons --}}
            <div>
                <div class="text-sm font-semibold text-slate-700 mb-2">Lý do phổ biến</div>
                <div class="grid grid-cols-2 gap-2">
                    @foreach(['Bệnh', 'Về quê', 'Gia đình có việc', 'Đi học thêm', 'Dự lễ nơi khác', 'Trời mưa'] as $reason)
                    <button
                        wire:click="$set('attendanceNote', '{{ $reason }}')"
                        class="px-3 py-2 bg-white border border-slate-200 rounded-lg
                               text-sm text-slate-700 hover:bg-slate-50 hover:border-yellow-300
                               transition-all text-left">
                        {{ $reason }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Info Notice --}}
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-blue-600 mt-0.5">
                        Thông tin này sẽ được lưu tạm. Nhấn <strong>"Lưu điểm danh"</strong> ở trên để lưu vào cơ sở dữ liệu.
                    </p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
            <button
                wire:click="closeNoteModal"
                class="px-4 py-2 bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                Hủy
            </button>
            <button
                wire:click="saveAttendanceWithNote"
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-yellow-500 text-white rounded-xl text-sm font-medium hover:bg-yellow-600
                       transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                <svg wire:loading.remove wire:target="saveAttendanceWithNote" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <svg wire:loading wire:target="saveAttendanceWithNote" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="saveAttendanceWithNote">Xác nhận</span>
                <span wire:loading wire:target="saveAttendanceWithNote">Đang lưu...</span>
            </button>
        </div>
    </div>
</div>
@endif

{{-- Unsaved Changes Indicator --}}
@if($hasUnsavedChanges)
<div class="fixed top-4 right-4 bg-amber-500 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 animate-pulse">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>
    <span>{{ $pendingCount }} thay đổi chưa lưu</span>
</div>
@endif

@push('scripts')
<script>
    document.addEventListener('livewire:load', function() {
        function detectViewMode() {
            const isMobile = window.innerWidth < 1024;
            Livewire.emit('viewModeDetected', isMobile ? 'mobile' : 'desktop');
        }

        detectViewMode();

        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(detectViewMode, 200);
        });

        window.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                @this.call('saveAttendance');
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    @keyframes indeterminate {
        0% {
            transform: translateX(-100%) scaleX(0.3);
        }

        50% {
            transform: translateX(0%) scaleX(0.7);
        }

        100% {
            transform: translateX(100%) scaleX(0.3);
        }
    }

    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
</style>
@endpush