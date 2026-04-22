@section('topbar')
<x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => route('dashboard')],
        ['label' => 'Điểm danh', 'url' => route('attendance.show')],
        ['label' => $selectedClassName],
    ]" />
@endsection

<div class="bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">

    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    @php
    $selectedClassName = $this->selectedClassName;
    @endphp

    <div wire:key="attendance-{{ $selectedClassId }}-{{ $attendanceType }}-{{ $selectedDate }}"
        x-data="{
            records: {},
            draft: {},
            isSaving: false,

            getStatus(studentId, sessionId) {
                const key = studentId + '_' + sessionId;
                if (this.draft[key] !== undefined) return this.draft[key].status;
                return this.records[key]?.status ?? null;
            },

            getNote(studentId, sessionId) {
                const key = studentId + '_' + sessionId;
                if (this.draft[key] !== undefined) return this.draft[key].note || null;
                return this.records[key]?.note || null;
            },

            hasDraft() { 
                return Object.keys(this.draft).length > 0;  
            },

            draftCount() { return Object.keys(this.draft).length; },

            toggle(studentId, sessionId, status) {
                const key     = studentId + '_' + sessionId;
                const current = this.getStatus(studentId, sessionId);

                if (current === status) {
                    delete this.draft[key];
                } else {
                    this.draft[key] = { status: status, note: '' };
                }
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
                if (!this.hasDraft() || this.isSaving) return;
                this.isSaving = true;
                $wire.saveFromClient(this.draft);
            },

            discard() { this.draft = {}; },

            onSaved(detail) { 
                this.draft = {}; 
                this.isSaving = false;
                if (detail && detail.records) {
                    Object.keys(this.records).forEach(k => delete this.records[k]);
                    // Gán key mới — Alpine detect mutation
                    Object.assign(this.records, detail.records);
                }
            },

            onRecordsLoaded(newRecords) {
            // Tương tự
            Object.keys(this.records).forEach(k => delete this.records[k]);
            Object.assign(this.records, newRecords);
            },

            onCleared() {
                this.draft = {};
                this.records = {};
            },

            onNoteSaved({ key, status, note }) {
                this.draft[key] = { status: status, note: note };
            },

            onSavingStarted() { this.isSaving = true; },
            onSavingCompleted() { this.isSaving = false; },

            noteModal: {
                open: false,
                studentId: null,
                sessionId: null,
                studentName: '',
                note: '',
            },

            openNoteAlpine(studentId, sessionId, studentName) {
                const key = studentId + '_' + sessionId;
                this.noteModal = {
                    open: true,
                    studentId,
                    sessionId,
                    studentName,
                    note: this.draft[key]?.note || this.records[key]?.note || '',
                };
            },

            saveNote() {
                const key = this.noteModal.studentId + '_' + this.noteModal.sessionId;
                this.draft[key] = { status: 2, note: this.noteModal.note };
                this.noteModal.open = false;
            },
        }"
        x-init="records = @js($attendanceRecords ?? []);"
        x-on:attendance-records-loaded.window="onRecordsLoaded($event.detail.records)"
        x-on:attendance-saved.window="onSaved($event.detail)"
        x-on:attendance-state-cleared.window="onCleared()"
        x-on:note-saved.window="onNoteSaved($event.detail)"
        x-on:saving-attendance.window="onSavingStarted()"
        x-on:attendance-save-completed.window="onSavingCompleted()">

        <div class="mx-auto max-w-7xl space-y-5">
            {{-- Main Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                {{-- Header --}}
                @if ($this->viewMode != 'mobile')
                <x-page-header
                    title="Điểm danh{{ $selectedClassId ? ' - ' . $selectedClassName : '' }}"
                    description="Điểm danh {{ $attendanceType == 1 ? 'đi học' : 'đi lễ' }}{{ $selectedClassId ? ' • ' . $students->count() . ' học sinh • ' . count($sessions) . ' buổi' : '' }}"
                    :stat-value="$students->count()"
                    stat-label="Học sinh"
                    icon-type="attendance">
                </x-page-header>
                @else
                <div id="page-big-title" class="px-4 pt-5 pb-3 transition-opacity duration-300">
                    <h1 class="text-2xl font-bold text-slate-800">
                        Điểm danh{{ $selectedClassId ? ' · ' . $selectedClassName : '' }}
                    </h1>
                    <p class="text-sm text-slate-500 mt-0.5">
                        {{ $attendanceType == 1 ? 'Đi học' : 'Đi lễ' }}
                        {{ $selectedClassId ? ' · ' . $students->count() . ' học sinh' : '' }}
                    </p>
                </div>
                @endif

                {{-- Actions Bar --}}
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/70">
                    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                        {{-- LEFT: Filters --}}
                        <div class="flex flex-col gap-3 w-full lg:flex-row lg:items-center">
                            @php $isAdmin = !auth()->user()->isCatechist(); @endphp
                            <livewire:filters.filter-bar
                                :parish-id="$parishId"
                                :show-nam-hoc="$isAdmin"
                                :show-khoi="$isAdmin"
                                :show-lop="true"
                                :show-ky="$isAdmin"
                                :selected-nam-hoc="$selectedNamHoc"
                                :selected-khoi="$selectedKhoi"
                                :selected-lop="$selectedClassId"
                                :selected-ky="$selectedKy" />

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
                                :disabled="!hasDraft() || isSaving"
                                :class="!hasDraft() || isSaving
                                ? 'bg-slate-200 text-slate-400 cursor-not-allowed'
                                : 'bg-primary-600 hover:bg-primary-700 text-white cursor-pointer'"
                                class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
                                <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                <svg x-show="isSaving" x-cloak class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-show="!isSaving">Lưu điểm danh</span>
                                <span x-show="isSaving" x-cloak>Đang lưu...</span>
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Tabs --}}
                @if($selectedClassId)
                <div class="flex rounded-b-xl bg-slate-200 p-1 text-sm font-medium">
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
                                            :class="getStatus({{ $student->id }}, {{ $session['id'] }}) == 1
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
                                                x-on:click="openNoteAlpine(
                                                    {{ $student->id }},
                                                    {{ $session['id'] }},
                                                    '{{ addslashes($student->full_name) }}'
                                                )"
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
                        <div
                            x-data
                            x-init="
                                $nextTick(() => {
                                    const active = $el.querySelector('[data-active=true]');
                                    if (active) {
                                        active.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' });
                                    }
                                })
                            "
                            class="flex gap-2 overflow-x-auto px-3 py-3 scrollbar-hide snap-x snap-mandatory">
                            @foreach($sessions as $session)
                            @php
                            $isActive = $session['dateStr'] === $selectedDate;
                            $hasAnyRecord = isset($sessionHasRecord[$session['dateStr']])
                            && $sessionHasRecord[$session['dateStr']] > 0;
                            @endphp
                            <button
                                data-active="{{ $isActive ? 'true' : 'false' }}"
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
                                                <div class="text-xs text-slate-400 leading-tight mt-0.5">
                                                    {{ $student->saint_name }}
                                                </div>
                                                <div class="text-sm font-semibold text-slate-900 leading-tight">
                                                    {{ $student->full_name }}
                                                </div>
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
                                                x-on:click="openNoteAlpine(
                                                    {{ $student->id }},
                                                    {{ $mobileSessionId }},
                                                    '{{ addslashes($student->full_name) }}'
                                                )"
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
                                :disabled="!hasDraft() || isSaving"
                                :class="!hasDraft() || isSaving
                                ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                                : 'bg-primary-600 text-white shadow-md active:scale-95'"
                                class="flex-1 h-14 rounded-xl font-semibold text-sm transition-all
                                   flex items-center justify-center gap-2">
                                <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                <svg x-show="isSaving" x-cloak class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-show="!isSaving">Lưu điểm danh</span>
                                <span x-show="isSaving" x-cloak>Đang lưu...</span>
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

        {{-- Note Modal — Alpine only --}}
        <div
            x-show="noteModal.open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed inset-0 bg-black/40 z-50 flex items-end lg:items-center justify-center lg:p-4"
            x-on:click="noteModal.open = false">

            {{-- Sheet / Modal --}}
            <div
                x-show="noteModal.open"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                x-transition:enter-end="translate-y-0 lg:scale-100 lg:opacity-100"
                class="w-full lg:w-[480px] lg:rounded-2xl rounded-t-2xl bg-white"
                x-on:click.stop>

                {{-- Handle — mobile only --}}
                <div class="flex justify-center pt-3 pb-2 lg:hidden">
                    <div class="w-10 h-1 bg-slate-300 rounded-full"></div>
                </div>

                {{-- Header — desktop only --}}
                <div class="hidden lg:flex items-center justify-between px-5 py-4 border-b border-slate-200">
                    <div>
                        <h3 class="font-semibold text-slate-900">Vắng có phép</h3>
                        <p class="text-sm text-slate-500 mt-0.5" x-text="noteModal.studentName"></p>
                    </div>
                    <button
                        type="button"
                        x-on:click="noteModal.open = false"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Student name — mobile only --}}
                <div class="px-4 pb-3 lg:hidden">
                    <span class="text-xs text-slate-400">Vắng có phép · </span>
                    <span class="text-sm font-semibold text-slate-700" x-text="noteModal.studentName"></span>
                </div>

                {{-- Quick reasons --}}
                <div class="px-4 lg:px-5 lg:py-4 grid grid-cols-2 gap-2">
                    @foreach(['Bệnh', 'Về quê', 'Gia đình có việc', 'Đi học thêm', 'Dự lễ nơi khác', 'Lý do khác'] as $reason)
                    <button
                        type="button"
                        x-on:click="noteModal.note = '{{ $reason }}'; saveNote()"
                        class="py-3 bg-slate-50 border border-slate-200 rounded-xl
                       text-sm text-slate-700 hover:bg-yellow-50 hover:border-yellow-300
                       active:scale-95 transition-all font-medium">
                        {{ $reason }}
                    </button>
                    @endforeach
                </div>

                {{-- Nhập tay --}}
                <div class="px-4 lg:px-5 pt-3 lg:pb-5 flex gap-2"
                    style="padding-bottom: calc(1rem + env(safe-area-inset-bottom))">
                    <input
                        type="text"
                        x-model="noteModal.note"
                        x-on:keydown.enter="if(noteModal.note.trim()) saveNote()"
                        placeholder="Lý do khác..."
                        class="flex-1 px-3 py-2.5 rounded-xl border border-slate-300 text-sm
                       focus:outline-none focus:ring-2 focus:ring-yellow-500" />
                    <button
                        type="button"
                        x-on:click="if(noteModal.note.trim()) saveNote()"
                        :class="noteModal.note.trim()
                    ? 'bg-yellow-500 hover:bg-yellow-600 text-white active:scale-95'
                    : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                        class="px-4 py-2.5 rounded-xl text-sm font-semibold transition-all">
                        OK
                    </button>
                </div>

            </div>
        </div>
    </div>

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

@push('page-title')
<span class="text-slate-800 font-semibold text-sm">Điểm danh</span>
@endpush

@push('scripts')
<script>
    // Collapsing header — IntersectionObserver
    (function() {
        function initCollapsingHeader() {
            const bigTitle = document.getElementById('page-big-title');
            const headerTitle = document.getElementById('header-collapsed-title');

            if (!bigTitle || !headerTitle) return;

            const observer = new IntersectionObserver(
                ([entry]) => {
                    if (entry.isIntersecting) {
                        // Big title visible → ẩn header title
                        headerTitle.style.opacity = '0';
                        bigTitle.style.opacity = '1';
                    } else {
                        // Big title out of view → hiện header title
                        headerTitle.style.opacity = '1';
                        bigTitle.style.opacity = '0';
                    }
                }, {
                    threshold: 0,
                    rootMargin: '-56px 0px 0px 0px', // trừ đi chiều cao header
                }
            );

            observer.observe(bigTitle);
        }

        // Init sau khi Livewire render xong
        document.addEventListener('livewire:load', initCollapsingHeader);
        document.addEventListener('livewire:update', initCollapsingHeader);
    })();

    document.addEventListener('livewire:load', function() {
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
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
</style>
@endpush