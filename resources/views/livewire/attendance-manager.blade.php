<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">

    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    @php
    $selectedClassName = $this->selectedClassName;
    @endphp

    {{-- Loading Indicator --}}
    <div
        wire:loading
        wire:loading.delay.shortest
        class="fixed top-0 left-0 right-0 z-[9999] pointer-events-none">
        <div class="h-0.5 bg-primary-100 overflow-hidden">
            <div class="h-full bg-primary-500 animate-[indeterminate_1.4s_ease-in-out_infinite]"></div>
        </div>
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

    <div wire:key="attendance-{{ $selectedClassId }}-{{ $attendanceType }}-{{ $viewMode }}--{{$selectedDate}}"
        x-data="{
            {{-- records: @js($attendanceRecords ?? []), --}}
            records: {},
            draft: {},

            getStatus(studentId, sessionId) {
                const key = studentId + '_' + sessionId;
                if (this.draft[key] !== undefined) return this.draft[key].status;
                const record = this.records[key];
                if (record !== undefined) {
                console.log('[getStatus] record raw:', JSON.stringify(record), 'type:', typeof record);
                }
                return this.records[key]?.status ?? null;
            },

            getNote(studentId, sessionId) {
                const key = studentId + '_' + sessionId;
                if (this.draft[key] !== undefined) return this.draft[key].note || null;
                return this.records[key]?.note || null;
            },

            hasDraft() { return Object.keys(this.draft).length > 0; },
            draftCount() { return Object.keys(this.draft).length; },

            toggle(studentId, sessionId, status) {
                console.log('[toggle] called', studentId, sessionId, status);
                const key     = studentId + '_' + sessionId;
                const current = this.getStatus(studentId, sessionId);
                console.log('[toggle] current:', current, 'new:', status);
                if (current === status) {
                    delete this.draft[key];
                } else {
                    this.draft[key] = { status: status, note: '' };
                }
                    console.log('[toggle] draft after:', JSON.stringify(this.draft));
            },

            openNote(studentId, sessionId) {
                $wire.openNoteModal(studentId, sessionId);
            },

            markAll(sessionId, studentIds) {
                studentIds.forEach(id => {
                    this.draft[id + '_' + sessionId] = { status: 1, note: '' };
                });
            },

            save() {
                if (!this.hasDraft()) return;
                console.log('[1] draft gửi lên:', JSON.stringify(this.draft));
                console.log('[1] số lượng:', this.draftCount());
                $wire.saveFromClient(this.draft);
            },

            discard() { this.draft = {}; },

            onSaved(detail) { 
                console.log('[4] onSaved fired, records nhận về:', detail);
                console.log('[4] draft TRƯỚC khi xóa:', JSON.stringify(this.draft));
                this.draft = {}; 
                if (detail && detail.records) {
                    // Xóa hết key cũ
                    console.log('[4] draft SAU khi xóa:', JSON.stringify(this.draft));
                    Object.keys(this.records).forEach(k => delete this.records[k]);
                    // Gán key mới — Alpine detect mutation
                    Object.assign(this.records, detail.records);
                    console.log('[4] records sau assign:', JSON.stringify(this.records));
                }
            },

            onRecordsLoaded(newRecords) {
            console.log('[onRecordsLoaded] fired, count:', Object.keys(newRecords).length);
            console.log('[onRecordsLoaded] sample:', JSON.stringify(Object.entries(newRecords).slice(0, 3)));
            // Tương tự
            Object.keys(this.records).forEach(k => delete this.records[k]);
            Object.assign(this.records, newRecords);
            console.log('[onRecordsLoaded] records sau assign:', Object.keys(this.records).length);
            },

            onCleared() {
                this.draft = {};
                this.records = {};
            },

            onNoteSaved({ key, status, note }) {
                this.draft[key] = { status: status, note: note };
            },
        }"
        x-init="records = @js($attendanceRecords ?? []); console.log('[x-init] records count:', Object.keys(records).length);
    console.log('[x-init] sample:', JSON.stringify(Object.entries(records).slice(0, 2)));
    console.log('[x-init] mobileSessionId from PHP:', {{ $mobileSessionId ?? 'null' }});"
        x-on:attendance-records-loaded.window="onRecordsLoaded($event.detail.records)"
        x-on:attendance-saved.window="onSaved($event.detail)"
        x-on:attendance-state-cleared.window="onCleared()"
        x-on:note-saved.window="onNoteSaved($event.detail)">

        <div class="mx-auto max-w-7xl space-y-5">

            {{-- Breadcrumb --}}
            <div class="hidden lg:block">
                <x-breadcrumb
                    :items="[
                    ['label' => 'Trang chủ', 'url' => route('dashboard')],
                    ['label' => 'Điểm danh', 'url' => route('attendance.show')],
                    ['label' => $selectedClassName],
                ]"
                    separator="arrow" />
            </div>

            {{-- Toast Notifications --}}
            <div role="status" aria-live="polite">
                @if (session()->has('message'))
                <x-toast-notification type="success" :duration="3500">{{ session('message') }}</x-toast-notification>
                @endif
                @if (session()->has('error'))
                <x-toast-notification type="error" :duration="4000">{{ session('error') }}</x-toast-notification>
                @endif
                @if (session()->has('warning'))
                <x-toast-notification type="warning" :duration="4000">{{ session('warning') }}</x-toast-notification>
                @endif
                @if (session()->has('info'))
                <x-toast-notification type="info" :duration="3500">{{ session('info') }}</x-toast-notification>
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
                            <div class="hidden lg:block">
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
                            </div>

                            <div class="lg:hidden">
                                <livewire:filters.filter-bar
                                    :parish-id="$parishId"
                                    :show-nam-hoc="false"
                                    :show-khoi="false"
                                    :show-lop="true"
                                    :show-ky="false"
                                    :selected-nam-hoc="$selectedNamHoc"
                                    :selected-khoi="$selectedKhoi"
                                    :selected-lop="$selectedClassId"
                                    :selected-ky="$selectedKy" />
                            </div>

                            <input
                                wire:model.live.debounce.500ms="search"
                                placeholder="Tìm học sinh..."
                                class="hidden lg:block w-56 px-3 py-2 rounded-xl
                                border border-slate-300 text-sm focus:outline-none
                                focus:ring-2 focus:ring-primary-500" />
                        </div>

                        {{-- RIGHT: Actions desktop — Alpine quản lý trạng thái --}}
                        @if($selectedClassId)
                        <div class="hidden lg:flex items-center gap-3">
                            <button
                                x-show="hasDraft()"
                                x-cloak
                                x-on:click="discard()"
                                class="px-4 py-2 border border-red-300 text-red-700 rounded-xl hover:bg-red-50
                                   transition-colors text-sm font-medium flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Hủy (<span x-text="draftCount()"></span>)
                            </button>

                            <button
                                x-on:click="save()"
                                :disabled="!hasDraft()"
                                :class="hasDraft()
                                ? 'bg-primary-600 hover:bg-primary-700 text-white cursor-pointer'
                                : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                                class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                Lưu điểm danh
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Tabs --}}
                @if($selectedClassId)
                <div class="bg-primary-50 p-1 flex gap-1 border-b border-slate-200">
                    <button
                        wire:click="switchType(1)"
                        class="flex-1 py-2 rounded-lg text-sm font-semibold transition-all
                        {{ $attendanceType == 1 ? 'bg-white text-primary-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Điểm danh đi học
                    </button>
                    <button
                        wire:click="switchType(2)"
                        class="flex-1 py-2 rounded-lg text-sm font-semibold transition-all
                        {{ $attendanceType == 2 ? 'bg-white text-primary-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        Điểm danh đi lễ
                    </button>
                </div>
                @endif
            </div>

            {{-- ==================== CONTENT ==================== --}}

            @if (!$selectedClassId)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
                <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <p class="mt-4 text-lg text-slate-500">Vui lòng chọn lớp để bắt đầu điểm danh</p>
            </div>

            @elseif ($students->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
                <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="mt-4 text-lg text-slate-500">Lớp chưa có học sinh</p>
            </div>

            @else
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                @if($students->count() > 0 && count($sessions) > 0)

                {{-- ===================== DESKTOP TABLE ===================== --}}
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
                                        {{-- ✅ Alpine xử lý markAll --}}
                                        <button
                                            x-on:click="markAll({{ $session['id'] }}, @js($students->pluck('id')->toArray()))"
                                            class="text-[9px] text-green-600 hover:text-green-700 hover:underline">
                                            ✓ Tất cả
                                        </button>
                                        @endif
                                    </div>
                                </x-table-header>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @foreach ($students as $index => $student)
                            <tr class="hover:bg-slate-50 transition-colors"
                                wire:key="student-{{ $student->id }}">

                                <td class="px-6 py-4 text-sm text-slate-500 sticky left-0 bg-white z-10">
                                    {{ $index + 1 }}
                                </td>

                                <td class="px-6 py-4 sticky left-12 bg-white z-10">
                                    <div class="text-xs text-slate-500">{{ $student->saint_name }}</div>
                                    <div class="font-semibold text-slate-900">{{ $student->full_name }}</div>
                                </td>

                                @foreach($sessions as $session)
                                <td class="px-3 py-3 text-center"
                                    wire:key="cell-{{ $student->id }}-{{ $session['id'] }}">

                                    @if($session['locked'])
                                    {{-- READ-ONLY: server data, không cần Alpine --}}
                                    @php
                                    $dbKey = $student->id . '_' . $session['id'];
                                    $dbStatus = $attendanceGrid[$student->id][$session['id']] ?? null;
                                    $dbNote = $attendanceRecords[$dbKey]['note'] ?? null;
                                    @endphp
                                    <div class="flex items-center justify-center h-8" x-data="{ open: false }">
                                        @if($dbStatus == 1)
                                        <span class="text-green-700 font-medium">✓</span>
                                        @elseif($dbStatus == 2)
                                        <div class="relative inline-block">
                                            <button
                                                @mouseenter="open = true"
                                                @mouseleave="open = false"
                                                class="text-yellow-700 font-medium cursor-help flex items-center gap-0.5">
                                                P
                                                @if($dbNote)
                                                <span class="w-1 h-1 bg-primary-500 rounded-full"></span>
                                                @endif
                                            </button>
                                            @if($dbNote)
                                            <div x-show="open" x-transition x-cloak
                                                class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 w-64
                                                       p-3 bg-slate-900 text-white text-xs rounded-lg shadow-xl z-30">
                                                <div class="font-semibold mb-1">Lý do vắng:</div>
                                                <div class="text-slate-200">{{ $dbNote }}</div>
                                                <div class="absolute left-1/2 -translate-x-1/2 top-full w-0 h-0
                                                            border-l-4 border-r-4 border-t-4
                                                            border-l-transparent border-r-transparent border-t-slate-900"></div>
                                            </div>
                                            @endif
                                        </div>
                                        @elseif($dbStatus == 3)
                                        <span class="text-red-700 font-medium">✕</span>
                                        @else
                                        <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </div>

                                    @else
                                    {{-- ✅ INTERACTIVE — Alpine xử lý hoàn toàn, không gọi Livewire --}}
                                    <div class="flex gap-1 justify-center">
                                        {{-- Có mặt --}}
                                        <button
                                            x-on:click="toggle({{ $student->id }}, {{ $session['id'] }}, 1)"
                                            :class="(() => {
                                                const s = getStatus({{ $student->id }}, {{ $session['id'] }});
                                                console.log('[render] student={{ $student->id }} session={{ $session['id'] }} status=' + s + ' type=' + typeof s);
                                                return s == 1
                                                    ? 'bg-green-500 text-white shadow-md scale-105'
                                                    : 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100';
                                            })()"
                                            class="px-2 py-1 rounded text-xs font-medium transition-all"
                                            aria-label="Có mặt">
                                            ✓
                                        </button>

                                        {{-- Vắng có phép --}}
                                        <div class="relative inline-block" x-data="{ open: false }">
                                            <button
                                                x-on:click="openNote({{ $student->id }}, {{ $session['id'] }})"
                                                @mouseenter="open = true"
                                                @mouseleave="open = false"
                                                :class="getStatus({{ $student->id }}, {{ $session['id'] }}) == 2
                                                ? 'bg-yellow-400 text-slate-900 shadow-md scale-105'
                                                : 'bg-amber-100 text-amber-800 border border-yellow-200 hover:bg-yellow-100'"
                                                class="px-2 py-1 rounded text-xs font-medium transition-all relative"
                                                aria-label="Vắng có phép">
                                                P
                                                <span
                                                    x-show="getNote({{ $student->id }}, {{ $session['id'] }})"
                                                    class="absolute -top-1 -right-1 w-2 h-2 bg-primary-500 rounded-full ring-2 ring-white">
                                                </span>
                                            </button>

                                            {{-- Tooltip note --}}
                                            <div
                                                x-show="open && getNote({{ $student->id }}, {{ $session['id'] }})"
                                                x-transition x-cloak
                                                class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 w-48
                                                   p-2 bg-slate-900 text-white text-[10px] rounded-lg shadow-xl z-30">
                                                <div class="font-semibold mb-1">Lý do:</div>
                                                <div class="text-slate-200 line-clamp-2"
                                                    x-text="getNote({{ $student->id }}, {{ $session['id'] }})">
                                                </div>
                                                <div class="absolute left-1/2 -translate-x-1/2 top-full w-0 h-0
                                                        border-l-4 border-r-4 border-t-4
                                                        border-l-transparent border-r-transparent border-t-slate-900"></div>
                                            </div>
                                        </div>

                                        {{-- Vắng không phép --}}
                                        <button
                                            x-on:click="toggle({{ $student->id }}, {{ $session['id'] }}, 3)"
                                            :class="getStatus({{ $student->id }}, {{ $session['id'] }}) == 3
                                            ? 'bg-red-500 text-white shadow-md scale-105'
                                            : 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100'"
                                            class="px-2 py-1 rounded text-xs font-medium transition-all"
                                            aria-label="Vắng không phép">
                                            ✕
                                        </button>
                                    </div>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach

                            {{-- Stats Row — server pre-computed --}}
                            <tr class="bg-slate-50 font-semibold border-t-2 border-slate-300">
                                <td colspan="2"
                                    class="px-6 py-3 text-sm text-slate-900 sticky left-0 bg-slate-50 z-10">
                                    Thống kê
                                </td>
                                @foreach($sessions as $session)
                                @php
                                $stats = $sessionStats[$session['dateStr']]
                                ?? ['present' => 0, 'absentPermitted' => 0, 'absentNotPermitted' => 0];
                                @endphp
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

                {{-- ===================== MOBILE VIEW ===================== --}}
                <div class="lg:hidden">
                    @php
                    $currentSession = collect($sessions)->firstWhere('dateStr', $selectedDate);
                    $locked = $currentSession['locked'] ?? false;
                    $mobileStats = $sessionStats[$selectedDate ?? '']
                    ?? ['present' => 0, 'absentPermitted' => 0, 'absentNotPermitted' => 0];
                    $total = $students->count();
                    $mobileSessionId = $currentSession['id'] ?? null;
                    @endphp

                    {{-- Date Selector Sticky --}}
                    <div class="sticky top-0 z-30 bg-white border-b border-slate-200 shadow-sm">
                        <div class="flex gap-2 overflow-x-auto px-3 py-3 scrollbar-hide snap-x snap-mandatory">
                            @foreach($sessions as $session)
                            @php
                            $isActive = $session['dateStr'] === $selectedDate;
                            $hasAnyRecord = isset($sessionHasRecord[$session['dateStr']])
                            && $sessionHasRecord[$session['dateStr']] > 0;
                            @endphp
                            <button
                                {{-- wire:click="$set('selectedDate', '{{ $session['dateStr'] }}')" --}}
                                wire:click="selectDate('{{ $session['dateStr'] }}')"
                                class="flex-shrink-0 snap-start flex flex-col items-center gap-1 px-3 py-2
                                   rounded-xl border transition-all min-w-[72px]
                                {{ $isActive
                                    ? 'bg-primary-600 border-primary-600 text-white shadow-md'
                                    : 'bg-white border-slate-200 text-slate-600 hover:border-primary-300' }}">
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
                                <span class="text-[10px] {{ $isActive ? 'text-white/80' : 'text-slate-400' }}">
                                    {{ $session['dayName'] }}
                                </span>
                                <span class="w-1.5 h-1.5 rounded-full mt-0.5
                                {{ $hasAnyRecord
                                    ? ($isActive ? 'bg-white/70' : 'bg-primary-400')
                                    : ($isActive ? 'bg-white/30' : 'bg-slate-200') }}">
                                </span>
                            </button>
                            @endforeach
                        </div>

                        {{-- Active session info bar --}}
                        @if($currentSession)
                        <div class="flex items-center justify-between px-4 py-2 bg-slate-50 border-t border-slate-100">
                            <div class="flex items-center gap-3 text-xs">
                                <span class="flex items-center gap-1 text-green-600 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                                    {{ $mobileStats['present'] }}
                                </span>
                                <span class="flex items-center gap-1 text-yellow-600 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span>
                                    {{ $mobileStats['absentPermitted'] }}
                                </span>
                                <span class="flex items-center gap-1 text-red-600 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                                    {{ $mobileStats['absentNotPermitted'] }}
                                </span>
                                <span class="text-slate-400">/ {{ $total }}</span>
                            </div>

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
                            {{-- ✅ Alpine markAll --}}
                            <button
                                x-on:click="markAll({{ $currentSession['id'] }}, @js($students->pluck('id')->toArray()))"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-green-700
                                   bg-green-100 hover:bg-green-200 rounded-full px-3 py-1 transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Tất cả có mặt
                            </button>
                            @endif
                        </div>
                        @endif
                    </div>

                    {{-- Mobile Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full border-separate border-spacing-0">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-full">
                                        Học sinh
                                        <span class="ml-1 font-normal text-slate-400">({{ $students->count() }})</span>
                                    </th>
                                    <th class="px-3 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">
                                        @if($locked)
                                        <span class="inline-flex items-center gap-1 text-slate-400">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            Đã khóa
                                        </span>
                                        @else
                                        Điểm danh
                                        @endif
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($students as $index => $student)
                                <tr wire:key="mobile-student-{{ $student->id }}-{{ $mobileSessionId }}"
                                    @if($mobileSessionId)
                                    :class="{
                                    'bg-green-50/40': getStatus({{ $student->id }}, {{ $mobileSessionId }}) == 1,
                                    'bg-red-50/30':   getStatus({{ $student->id }}, {{ $mobileSessionId }}) == 3,
                                }"
                                    @endif
                                    class="transition-colors">

                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center
                                            flex-shrink-0 text-xs font-semibold text-slate-500">
                                                {{ $index + 1 }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-slate-900 leading-tight">
                                                    {{ $student->full_name }}
                                                </div>
                                                @if($student->saint_name)
                                                <div class="text-xs text-slate-400 leading-tight mt-0.5">
                                                    {{ $student->saint_name }}
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td wire:key="mobile-cell-{{ $student->id }}-{{ $mobileSessionId }}" class="px-3 py-3">
                                        @if($locked)
                                        {{-- Read-only mobile --}}
                                        @php
                                        $mobileStatus = $mobileSessionId
                                        ? ($attendanceGrid[$student->id][$mobileSessionId] ?? null)
                                        : null;
                                        @endphp
                                        <div class="flex justify-center">
                                            @if($mobileStatus == 1)
                                            <span class="w-11 h-11 rounded-xl bg-green-500 flex items-center justify-center shadow-sm">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            </span>
                                            @elseif($mobileStatus == 2)
                                            <span class="w-11 h-11 rounded-xl bg-yellow-400 flex items-center justify-center shadow-sm">
                                                <span class="text-slate-900 font-bold text-base">P</span>
                                            </span>
                                            @elseif($mobileStatus == 3)
                                            <span class="w-11 h-11 rounded-xl bg-red-500 flex items-center justify-center shadow-sm">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </span>
                                            @else
                                            <span class="w-11 h-11 flex items-center justify-center text-slate-300 text-lg">—</span>
                                            @endif
                                        </div>

                                        @else
                                        {{-- ✅ Interactive mobile — Alpine --}}
                                        @if($mobileSessionId)
                                        <div class="flex gap-1.5 justify-center">
                                            <button
                                                {{-- x-on:click="toggle({{ $student->id }}, {{ $mobileSessionId }}, 1)" --}}
                                                x-on:click="
                                                    console.log('[B1] click event fired');
                                                    toggle({{ $student->id }}, {{ $mobileSessionId }}, 1)
                                                "
                                                :class="getStatus({{ $student->id }}, {{ $mobileSessionId }}) == 1
                                                ? 'bg-green-500 text-white shadow-md ring-2 ring-green-300'
                                                : 'bg-green-50 text-green-700 border border-green-200 active:bg-green-100'"
                                                class="w-11 h-11 rounded-xl flex items-center justify-center transition-all active:scale-95"
                                                aria-label="Có mặt">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>

                                            <button
                                                x-on:click="openNote({{ $student->id }}, {{ $mobileSessionId }})"
                                                :class="getStatus({{ $student->id }}, {{ $mobileSessionId }}) == 2
                                                ? 'bg-yellow-400 text-slate-900 shadow-md ring-2 ring-yellow-300'
                                                : 'bg-yellow-50 text-yellow-700 border border-yellow-200 active:bg-yellow-100'"
                                                class="w-11 h-11 rounded-xl flex items-center justify-center font-bold text-base transition-all active:scale-95"
                                                aria-label="Vắng có phép">
                                                P
                                            </button>

                                            <button
                                                x-on:click="toggle({{ $student->id }}, {{ $mobileSessionId }}, 3)"
                                                :class="getStatus({{ $student->id }}, {{ $mobileSessionId }}) == 3
                                                ? 'bg-red-500 text-white shadow-md ring-2 ring-red-300'
                                                : 'bg-red-50 text-red-700 border border-red-200 active:bg-red-100'"
                                                class="w-11 h-11 rounded-xl flex items-center justify-center transition-all active:scale-95"
                                                aria-label="Vắng không phép">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        @endif
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Sticky Bottom Bar --}}
                    <div class="lg:hidden fixed left-0 right-0 z-40
                    bg-white border-t border-slate-200 shadow-lg px-4"
                        style="bottom: calc(env(safe-area-inset-bottom) + 60px); padding-bottom: 12px; padding-top: 12px;">
                        <div class="flex items-center gap-3 max-w-7xl mx-auto">

                            {{-- QR --}}
                            @if($selectedClassId && $currentSession && !$locked)
                            <a href="{{ route('attendance.qr', [
                                'classId'   => $selectedClassId,
                                'sessionId' => $currentSession['id'],
                                'type'      => $attendanceType,
                            ]) }}"
                                class="flex-shrink-0 w-14 h-14 rounded-xl border border-slate-200
                                   flex items-center justify-center text-slate-600 hover:bg-slate-50 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                            </a>
                            @endif

                            {{-- Discard --}}
                            <button
                                x-show="hasDraft()"
                                x-cloak
                                x-on:click="discard()"
                                class="flex-shrink-0 w-14 h-14 rounded-xl border border-red-200
                                   flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            {{-- Save --}}
                            <button
                                x-on:click="save()"
                                :disabled="!hasDraft()"
                                :class="hasDraft()
                                ? 'bg-primary-600 text-white shadow-md active:scale-95'
                                : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                                class="flex-1 h-14 rounded-xl font-semibold text-sm transition-all
                                   flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                Lưu điểm danh
                                <span
                                    x-show="hasDraft()"
                                    x-text="draftCount()"
                                    class="bg-white/20 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="lg:hidden h-32"></div>
                </div>

                @else
                <div class="text-center py-12">
                    <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <p class="mt-4 text-lg text-slate-500">
                        {{ empty($sessions) ? 'Chưa có buổi điểm danh nào' : 'Không có dữ liệu để hiển thị' }}
                    </p>
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>

    {{-- ===================== NOTE MODAL ===================== --}}
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

            <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-yellow-50 to-white">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h2 id="note-modal-title" class="text-xl font-bold text-slate-900">Vắng có phép</h2>
                        <p class="text-sm text-slate-600 mt-1">Ghi chú lý do vắng mặt</p>
                    </div>
                    <button wire:click="closeNoteModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-5">
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

                <div x-data="{ count: {{ strlen($attendanceNote) }} }">
                    <label for="attendance-note" class="block text-sm font-semibold text-slate-700 mb-2">
                        Lý do vắng <span class="text-slate-400 font-normal">(không bắt buộc)</span>
                    </label>
                    <textarea
                        id="attendance-note"
                        wire:model.defer="attendanceNote"
                        x-on:input="count = $el.value.length"
                        rows="4"
                        placeholder="Vd: Bệnh, về quê, đi học thêm, gia đình có việc..."
                        class="w-full px-4 py-3 rounded-xl border border-slate-300
                           focus:outline-none focus:ring-2 focus:ring-yellow-500 resize-none text-sm"
                        maxlength="500"></textarea>
                    <div class="mt-1 text-xs text-slate-500 flex items-center justify-between">
                        <span>Tối đa 500 ký tự</span>
                        <span x-text="count + '/500'"></span>
                    </div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-slate-700 mb-2">Lý do phổ biến</div>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['Bệnh', 'Về quê', 'Gia đình có việc', 'Đi học thêm', 'Dự lễ nơi khác', 'Trời mưa'] as $reason)
                        <button
                            x-on:click="$wire.set('attendanceNote', '{{ $reason }}')"
                            class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-700
                               hover:bg-slate-50 hover:border-yellow-300 transition-all text-left">
                            {{ $reason }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-500 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-blue-600 mt-0.5">
                            Thông tin này sẽ được lưu tạm. Nhấn <strong>"Lưu điểm danh"</strong> để lưu vào cơ sở dữ liệu.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                <button
                    wire:click="closeNoteModal"
                    class="px-4 py-2 bg-white border border-slate-300 rounded-xl text-sm font-medium
                       text-slate-700 hover:bg-slate-50 transition-colors">
                    Hủy
                </button>
                <button
                    wire:click="saveAttendanceWithNote"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 bg-yellow-500 text-white rounded-xl text-sm font-medium hover:bg-yellow-600
                       transition-colors disabled:opacity-50 flex items-center gap-2">
                    <svg wire:loading.remove wire:target="saveAttendanceWithNote"
                        class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg wire:loading wire:target="saveAttendanceWithNote"
                        class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="saveAttendanceWithNote">Xác nhận</span>
                    <span wire:loading wire:target="saveAttendanceWithNote">Đang lưu...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Unsaved Changes Indicator desktop --}}
    <div
        x-show="hasDraft()"
        x-cloak
        class="hidden lg:flex fixed top-4 right-4 bg-amber-500 text-white px-4 py-3
           rounded-xl shadow-lg items-center gap-3 z-40">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span x-text="draftCount() + ' thay đổi chưa lưu'"></span>
    </div>
</div>>

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

        // Ctrl+S / Cmd+S → save
        window.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                // Tìm Alpine component và gọi save()
                const el = document.querySelector('[x-data]');
                if (el) {
                    Alpine.evaluate(el, 'save()');
                }
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