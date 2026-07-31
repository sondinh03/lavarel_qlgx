@section('topbar')
<x-breadcrumb :items="array_values(array_filter([
        ['label' => 'Trang chủ', 'url' => auth()->user()->usesCatechistLayout() ? route('catechist.dashboard') : route('parish-admin.dashboard')],
        ['label' => 'Điểm danh', 'url' => route('attendance.show')],
        $subjectTarget === 'teachers'
            ? ['label' => 'Giáo lý viên']
            : ['label' => 'Học sinh' . ($selectedClassName ? ' · ' . $selectedClassName : '')],
    ]))" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));"
    x-data="{ showAbsentExport: false }"
    x-init="
        document.addEventListener('livewire:load', () => {
            Livewire.on('openAbsentExportModal', () => { showAbsentExport = true; });
            Livewire.on('closeAbsentExportModal', () => { showAbsentExport = false; });
            Livewire.on('confirmEarlyAttendanceExport', (message, exportKind) => {
                if (!confirm(message)) return;

                if (exportKind === 'absent') {
                    @this.call('exportAbsentStudents', true);
                    return;
                }

                @this.call('exportAttendance', true);
            });
        });
    ">

    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    @php
    $selectedClassName = $this->selectedClassName;
    @endphp

    <div id="main-content" class="mx-auto max-w-7xl">
        <x-mac-panel :overflow="true">
            @if ($this->viewMode != 'mobile')
            <x-page-header
                title="{{ $subjectTarget === 'teachers'
                    ? 'Điểm danh giáo lý viên'
                    : ('Điểm danh học sinh' . ($selectedClassId ? ' - ' . $selectedClassName : '')) }}"
                description="{{ $subjectTarget === 'teachers'
                    ? ('Điểm danh ' . (\App\Models\TeacherAttendanceSession::typeLabel((int) $attendanceType)) . ' • ' . ($teachers->count() ?? 0) . ' GLV • ' . count($sessions) . ' buổi')
                    : ('Điểm danh ' . ($attendanceType == 1 ? 'đi học' : 'đi lễ') . ($selectedClassId ? ' • ' . $students->count() . ' học sinh • ' . count($sessions) . ' buổi' : '')) }}"
                icon-type="attendance" />
            @else
            <div id="page-big-title" class="px-4 sm:px-6 pt-5 pb-3 mac-hairline-b transition-opacity duration-300">
                <h1 class="text-2xl font-bold text-slate-800">
                    @if($subjectTarget === 'teachers')
                        Điểm danh giáo lý viên
                    @else
                        Điểm danh học sinh{{ $selectedClassId ? ' · ' . $selectedClassName : '' }}
                    @endif
                </h1>
                <p class="text-sm text-slate-500 mt-0.5">
                    @if($subjectTarget === 'teachers')
                        {{ \App\Models\TeacherAttendanceSession::typeLabel((int) $attendanceType) }}
                        · {{ $teachers->count() ?? 0 }} GLV
                    @else
                        {{ $attendanceType == 1 ? 'Đi học' : 'Đi lễ' }}
                        {{ $selectedClassId ? ' · ' . $students->count() . ' học sinh' : '' }}
                    @endif
                </p>
            </div>
            @endif

            <div class="p-3 sm:p-4 lg:p-6 mac-hairline-b bg-white/30">
                @php
                    $isAdmin = auth()->user()->canManage();
                    $canCreateSessions = (bool) auth()->user()?->canCreateAttendanceSessions();
                @endphp

                @if($assignmentBlocked)
                <div class="mb-3 flex items-start gap-3 px-4 py-3 rounded-xl bg-amber-50/90 border border-amber-200/80">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm">
                        <p class="font-semibold text-amber-950">Bạn chưa được phân công lớp trong năm học này</p>
                        <p class="text-amber-800/80 mt-0.5">Liên hệ Ban quản trị giáo lý để được phân công trước khi điểm danh.</p>
                    </div>
                </div>
                @endif

                <div class="flex flex-col gap-2 lg:gap-4">
                    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-2 lg:gap-4">
                        @if($subjectTarget === 'teachers')
                        <livewire:filters.filter-bar
                            wire:key="attendance-teacher-filter-{{ $selectedNamHoc }}"
                            :parish-id="$parishId"
                            :show-nam-hoc="true"
                            :show-khoi="false"
                            :show-lop="false"
                            :show-ky="false"
                            :allow-all-year="false"
                            :selected-nam-hoc="$selectedNamHoc" />
                        @else
                        <livewire:filters.filter-bar
                            wire:key="attendance-filter-{{ $selectedClassId }}-{{ $selectedNamHoc }}-{{ $selectedKy }}"
                            :parish-id="$parishId"
                            :show-nam-hoc="$isAdmin"
                            :show-khoi="$isAdmin"
                            :show-lop="true"
                            :show-ky="true"
                            :allow-all-year="false"
                            :leave-guard="true"
                            :selected-nam-hoc="$selectedNamHoc"
                            :selected-khoi="$selectedKhoi"
                            :selected-lop="$selectedClassId"
                            :selected-ky="$selectedKy"
                            :allowed-class-ids="$this->filterAllowedClassIds" />
                        @endif

                        <div class="hidden lg:block w-72">
                            <x-search-input
                                placeholder="{{ $subjectTarget === 'teachers' ? 'Tìm GLV...' : 'Tìm học sinh...' }}"
                                wire-model="search"
                                debounce="500ms" />
                        </div>
                    </div>

                    <div class="lg:hidden">
                        <x-search-input
                            placeholder="{{ $subjectTarget === 'teachers' ? 'Tìm GLV...' : 'Tìm học sinh...' }}"
                            wire-model="search"
                            debounce="500ms" />
                    </div>
                </div>
            </div>

            {{-- Leave-guard bridge: ngoài wire:key, luôn nhận filter-leave-request --}}
            <div
                class="hidden"
                aria-hidden="true"
                x-data="{
                    onFilterLeave(raw) {
                        const detail = Array.isArray(raw) ? (raw[0] || {}) : (raw || {});
                        const root = document.querySelector('[data-attendance-root]');
                        let dirty = false;
                        if (root && window.Alpine) {
                            try { dirty = !!Alpine.evaluate(root, 'hasDraft()'); } catch (e) {}
                        }
                        const fb = detail.componentId ? window.Livewire.find(detail.componentId) : null;
                        const confirmFn = () => fb ? fb.call('confirmFilterLeave') : window.Livewire.emit('confirmFilterLeave');
                        const cancelFn = () => fb ? fb.call('cancelFilterLeave') : window.Livewire.emit('cancelFilterLeave');

                        if (dirty) {
                            const label = detail.actionLabel || 'đổi bộ lọc';
                            if (!confirm('Bạn có thay đổi chưa lưu. Nếu ' + label + ' sẽ mất thay đổi. Tiếp tục?')) {
                                cancelFn();
                                return;
                            }
                            try { Alpine.evaluate(root, 'resetEditingState()'); } catch (e) {}
                        }
                        confirmFn();
                    }
                }"
                x-on:filter-leave-request.window="onFilterLeave($event.detail)">
            </div>

    <div wire:ignore
        wire:key="attendance-v2-{{ $subjectTarget }}-{{ $selectedClassId }}-{{ $selectedNamHoc }}-{{ $attendanceType }}-{{ $selectedKy }}-{{ $selectedDate }}"
        data-attendance-root
        x-data="{
            records: {},
            draft: {},
            isSaving: false,
            context: @js($this->getClientContext()),
            livewireId: null,
            personIds: @js(($subjectTarget === 'teachers' ? collect($teachers ?? []) : collect($students ?? []))->pluck('id')->map(fn ($id) => (int) $id)->values()->all()),

            getLivewire() {
                return window.Livewire.find(this.livewireId);
            },

            toast(type, message) {
                this.getLivewire()?.emit('toast', type, message);
            },

            /** Bỏ phần date để so context khi đổi ngày (cùng lớp/type/kỳ). */
            contextBase(ctx) {
                return String(ctx || '').replace(/\|date:[^|]*/, '');
            },

            acceptContext(detail) {
                if (!detail?.context) return true;
                if (this.contextBase(detail.context) !== this.contextBase(this.context)) {
                    return false;
                }
                this.context = detail.context;
                return true;
            },

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

            isInferred(studentId, sessionId) {
                const key = studentId + '_' + sessionId;
                return this.draft[key] === undefined && this.records[key]?.inferred === true;
            },

            /** Thống kê live từ records+draft — cần vì wire:ignore chặn morph Blade. */
            sessionStat(sessionId, field) {
                let present = 0, absentPermitted = 0, absentNotPermitted = 0;
                (this.personIds || []).forEach(id => {
                    const s = Number(this.getStatus(id, sessionId));
                    if (s === 1) present++;
                    else if (s === 2) absentPermitted++;
                    else if (s === 3) absentNotPermitted++;
                });
                return ({ present, absentPermitted, absentNotPermitted })[field] ?? 0;
            },

            isDirtyEntry(key, item) {
                const saved = this.records[key];
                const status = item?.status ?? null;
                const note = item?.note || '';
                if (!saved) return status !== null && status !== undefined;
                return Number(saved.status) !== Number(status) || (saved.note || '') !== note;
            },

            hasDraft() {
                return Object.keys(this.draft).some(k => this.isDirtyEntry(k, this.draft[k]));
            },

            draftCount() {
                return Object.keys(this.draft).filter(k => this.isDirtyEntry(k, this.draft[k])).length;
            },

            pruneDraft() {
                Object.keys(this.draft).forEach(k => {
                    if (!this.isDirtyEntry(k, this.draft[k])) delete this.draft[k];
                });
            },

            clearDraft(showToast = false) {
                this.draft = {};
                if (showToast) this.toast('info', 'Đã hủy thay đổi');
            },

            resetEditingState() {
                this.isSaving = false;
                this.clearDraft(false);
            },

            confirmLeave(actionLabel = 'đổi trang') {
                if (!this.hasDraft()) return true;
                return confirm(
                    'Bạn có thay đổi chưa lưu. Nếu ' + actionLabel + ' sẽ mất thay đổi. Tiếp tục?'
                );
            },

            requestLeave(actionLabel, proceed) {
                if (!this.confirmLeave(actionLabel)) return false;
                this.resetEditingState();
                if (typeof proceed === 'function') proceed();
                return true;
            },

            toggle(studentId, sessionId, status) {
                const key = studentId + '_' + sessionId;
                const current = this.getStatus(studentId, sessionId);
                const statusNum = Number(status);

                if (current !== null && current !== undefined && Number(current) === statusNum) {
                    if (this.draft[key] !== undefined) delete this.draft[key];
                    return;
                }

                const existingNote = this.getNote(studentId, sessionId) || '';
                // Lý do chỉ gắn với vắng có phép (status 2)
                const next = {
                    status: statusNum,
                    note: statusNum === 2 ? existingNote : '',
                };

                if (!this.isDirtyEntry(key, next)) {
                    delete this.draft[key];
                    return;
                }

                this.draft[key] = next;
            },

            markAll(sessionId, studentIds) {
                const unmarked = studentIds.filter(id => this.getStatus(id, sessionId) == null);

                if (unmarked.length === 0) {
                    if (!confirm('Tất cả đã có trạng thái. Đánh dấu lại tất cả thành có mặt?')) return;
                    studentIds.forEach(id => {
                        const key = id + '_' + sessionId;
                        const next = { status: 1, note: '' };
                        if (!this.isDirtyEntry(key, next)) {
                            delete this.draft[key];
                            return;
                        }
                        this.draft[key] = next;
                    });
                    return;
                }

                unmarked.forEach(id => {
                    this.draft[id + '_' + sessionId] = { status: 1, note: '' };
                });
            },

            requestSwitchType(type) {
                if (Number(type) === Number(@js($attendanceType ?? 1))) return;
                this.requestLeave('đổi loại điểm danh', () => {
                    this.getLivewire().call('switchType', type);
                });
            },

            requestSelectDate(date) {
                if (date === @js($selectedDate)) return;
                this.requestLeave('đổi ngày', () => {
                    this.getLivewire().call('selectDate', date);
                });
            },

            requestExport() {
                this.requestLeave('xuất Excel', () => {
                    this.getLivewire().call('exportAttendance');
                });
            },

            requestAbsentExport() {
                this.requestLeave('xuất học sinh vắng', () => {
                    this.getLivewire().call('openAbsentExportModal');
                });
            },

            saveButtonLabel() {
                return this.isSaving ? 'Đang lưu…' : 'Lưu diểm danh';
            },

            save() {
                if (this.isSaving || !this.hasDraft()) return;
                this.isSaving = true;
                this.getLivewire().call('saveFromClient', this.draft);
            },

            discard() {
                if (!this.hasDraft() || this.isSaving) return;
                if (!confirm('Hủy các thay đổi chưa lưu?')) return;
                this.clearDraft(true);
            },

            onSaved(detail) {
                const savedKeys = Array.isArray(detail?.savedKeys) ? detail.savedKeys : [];
                const patches = detail?.patches || detail?.records || {};
                if (!this.acceptContext(detail)) return;
                this.isSaving = false;
                // Patch-merge only: wire:ignore giữ nguyên Alpine records còn lại
                savedKeys.forEach(k => {
                    if (patches[k] !== undefined) {
                        this.records[k] = patches[k];
                    }
                });
                if (savedKeys.length > 0) {
                    savedKeys.forEach(k => delete this.draft[k]);
                    this.pruneDraft();
                } else {
                    this.pruneDraft();
                }
            },

            onRecordsLoaded(detail) {
                if (!this.acceptContext(detail)) return;
                const newRecords = detail?.records || {};
                this.records = Object.assign({}, newRecords);
                this.pruneDraft();
            },

            onCleared() {
                this.resetEditingState();
                this.records = {};
            },

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
        x-init="
            livewireId = $el.closest('[wire\\:id]')?.getAttribute('wire:id');
            records = @js(($subjectTarget === 'teachers' ? ($teacherAttendanceRecords ?? []) : ($attendanceRecords ?? [])));
            draft = {};
            isSaving = false;

            const guardUnload = (e) => {
                if (!hasDraft()) return;
                e.preventDefault();
                return e.returnValue = '';
            };

            window.addEventListener('beforeunload', guardUnload);

            $cleanup(() => {
                window.removeEventListener('beforeunload', guardUnload);
            });
        "
        x-on:attendance-records-loaded.window="onRecordsLoaded($event.detail)"
        x-on:attendance-saved.window="onSaved($event.detail)"
        x-on:attendance-state-cleared.window="onCleared()"
        x-on:attendance-save-completed.window="onSavingCompleted()"
        x-on:attendance-export="requestExport()"
        x-on:attendance-absent-export="requestAbsentExport()">

            @php
                $isAdmin = auth()->user()->canManage();
                $canCreateSessions = (bool) auth()->user()?->canCreateAttendanceSessions();
            @endphp

            <div class="px-4 lg:px-6 py-3 mac-hairline-b bg-white/20">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 min-w-0">
                        @if($this->canMarkTeacherAttendance())
                        <div class="flex gap-1 p-1 rounded-xl bg-black/[0.04] border border-black/[0.04] w-fit flex-shrink-0">
                            <button
                                type="button"
                                wire:click="switchSubjectTarget('students')"
                                x-on:click="
                                    if (@js($subjectTarget ?? 'students') === 'students') { $event.stopImmediatePropagation(); return; }
                                    if (hasDraft() && !confirm('Bạn có thay đổi chưa lưu. Nếu đổi đối tượng điểm danh sẽ mất thay đổi. Tiếp tục?')) {
                                        $event.stopImmediatePropagation();
                                        $event.preventDefault();
                                        return;
                                    }
                                    resetEditingState();
                                "
                                class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all
                                       {{ $subjectTarget !== 'teachers'
                                           ? 'bg-white/90 text-primary-600 shadow-mac-sm'
                                           : 'text-slate-600 hover:text-slate-900' }}">
                                Học sinh
                            </button>
                            <button
                                type="button"
                                wire:click="switchSubjectTarget('teachers')"
                                x-on:click="
                                    if (@js($subjectTarget ?? 'students') === 'teachers') { $event.stopImmediatePropagation(); return; }
                                    if (hasDraft() && !confirm('Bạn có thay đổi chưa lưu. Nếu đổi đối tượng điểm danh sẽ mất thay đổi. Tiếp tục?')) {
                                        $event.stopImmediatePropagation();
                                        $event.preventDefault();
                                        return;
                                    }
                                    resetEditingState();
                                "
                                class="px-4 py-1.5 text-sm font-semibold rounded-lg transition-all
                                       {{ $subjectTarget === 'teachers'
                                           ? 'bg-white/90 text-primary-600 shadow-mac-sm'
                                           : 'text-slate-600 hover:text-slate-900' }}">
                                Giáo lý viên
                            </button>
                        </div>
                        @endif

                        @if($subjectTarget === 'teachers')
                        <div class="flex gap-1 p-1 rounded-xl bg-black/[0.04] border border-black/[0.04] w-fit flex-shrink-0">
                            <button type="button" x-on:click="requestSwitchType(1)"
                                class="px-3 sm:px-4 py-1.5 text-sm font-semibold rounded-lg transition-all
                                {{ (int) $attendanceType === 1 ? 'bg-white/90 text-primary-600 shadow-mac-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                Đi dạy
                            </button>
                            <button type="button" x-on:click="requestSwitchType(2)"
                                class="px-3 sm:px-4 py-1.5 text-sm font-semibold rounded-lg transition-all
                                {{ (int) $attendanceType === 2 ? 'bg-white/90 text-primary-600 shadow-mac-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                Đi lễ
                            </button>
                            <button type="button" x-on:click="requestSwitchType(3)"
                                class="px-3 sm:px-4 py-1.5 text-sm font-semibold rounded-lg transition-all
                                {{ (int) $attendanceType === 3 ? 'bg-white/90 text-primary-600 shadow-mac-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                Họp
                            </button>
                        </div>
                        @elseif($selectedClassId)
                        <div class="flex gap-1 p-1 rounded-xl bg-black/[0.04] border border-black/[0.04] w-fit flex-shrink-0">
                            <button type="button" x-on:click="requestSwitchType(1)"
                                class="px-3 sm:px-4 py-1.5 text-sm font-semibold rounded-lg transition-all
                                {{ (int) $attendanceType === 1 ? 'bg-white/90 text-primary-600 shadow-mac-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                Đi học
                            </button>
                            <button type="button" x-on:click="requestSwitchType(2)"
                                class="px-3 sm:px-4 py-1.5 text-sm font-semibold rounded-lg transition-all
                                {{ (int) $attendanceType === 2 ? 'bg-white/90 text-primary-600 shadow-mac-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                Đi lễ
                            </button>
                        </div>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-4 justify-end flex-shrink-0 w-full lg:w-auto">
                        @if($subjectTarget === 'teachers')
                            @if($isAdmin && $this->viewMode !== 'mobile')
                            <x-dropdown label="Khác" icon="grid" align="right" width="72" variant="secondary">
                                <x-dropdown-item
                                    icon="download"
                                    x-on:click="open = false; $dispatch('attendance-export')">
                                    Xuất tổng kết điểm danh GLV
                                </x-dropdown-item>
                                <x-dropdown-item
                                    icon="download"
                                    x-on:click="open = false; $dispatch('attendance-absent-export')">
                                    Xuất danh sách GLV vắng
                                </x-dropdown-item>
                            </x-dropdown>
                            @endif
                            @if($this->viewMode !== 'mobile')
                            <div class="flex items-center gap-4">
                                <x-button variant="secondary" size="sm" x-on:click="discard()"
                                    x-bind:disabled="isSaving || !hasDraft()">
                                    Hủy
                                </x-button>
                                <x-button variant="primary" size="sm" x-on:click="save()"
                                    x-bind:disabled="isSaving || !hasDraft()">
                                    <span x-text="saveButtonLabel()"></span>
                                </x-button>
                            </div>
                            @endif
                        @elseif($selectedClassId)
                            @php
                                $sessionManageUrl = route('session.index', array_filter([
                                    'namHoc'  => $selectedNamHoc,
                                    'classId' => $selectedClassId,
                                    'khoi'    => $selectedKhoi ?: null,
                                ]));
                                $statisticsUrl = route('attendance.statistics', [
                                    'namHoc'  => $selectedNamHoc,
                                    'classId' => $selectedClassId,
                                    'khoi'    => $selectedKhoi,
                                    'ky'      => $selectedKy,
                                    'type'    => $attendanceType,
                                ]);
                            @endphp

                            <x-dropdown
                                label="Khác"
                                icon="grid"
                                align="right"
                                width="72"
                                variant="secondary">
                                @if($canCreateSessions)
                                <x-dropdown-item
                                    as="a"
                                    :href="$sessionManageUrl"
                                    icon="calendar"
                                    x-on:click="open = false; if (!confirmLeave('quản lý phiên điểm danh')) { $event.preventDefault(); }">
                                    Quản lý phiên điểm danh
                                </x-dropdown-item>
                                @endif
                                <x-dropdown-item
                                    as="a"
                                    :href="$statisticsUrl"
                                    icon="clipboard"
                                    x-on:click="open = false; if (!confirmLeave('xem thống kê')) { $event.preventDefault(); return; } resetEditingState();">
                                    Thống kê
                                </x-dropdown-item>
                                @if($isAdmin)
                                <div class="h-px bg-slate-100 my-1"></div>
                                <x-dropdown-item
                                    icon="download"
                                    x-on:click="open = false; $dispatch('attendance-export')">
                                    Xuất tổng kết điểm danh học sinh
                                </x-dropdown-item>
                                <x-dropdown-item
                                    icon="download"
                                    x-on:click="open = false; $dispatch('attendance-absent-export')">
                                    Xuất danh sách học sinh vắng
                                </x-dropdown-item>
                                @endif
                            </x-dropdown>

                            @if($this->viewMode !== 'mobile')
                            <div class="flex items-center gap-4">
                                <x-button variant="secondary" size="sm" x-on:click="discard()"
                                    x-bind:disabled="isSaving || !hasDraft()">
                                    Hủy
                                </x-button>
                                <x-button variant="primary" size="sm" x-on:click="save()"
                                    x-bind:disabled="isSaving || !hasDraft()">
                                    <span x-text="saveButtonLabel()"></span>
                                </x-button>
                            </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            @if($subjectTarget === 'teachers')
            @if(!$selectedNamHoc)
            <x-stats.page-empty
                :panel="false"
                title="Chọn năm học để điểm danh GLV"
                description="Chọn năm học ở bộ lọc, tạo buổi (đi dạy / đi lễ / họp), rồi điểm danh."
                tone="primary">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </x-slot>
            </x-stats.page-empty>
            @elseif($teachers->isEmpty())
            <x-stats.page-empty
                :panel="false"
                title="Chưa có giáo lý viên"
                description="Thêm GLV ở menu Giáo lý viên trước khi điểm danh."
                tone="primary">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </x-slot>
                @if(auth()->user()->canManage())
                <x-button as="a" href="{{ route('catechists.index') }}" variant="primary">
                    <x-icon name="user-plus" />
                    Sang trang Giáo lý viên
                </x-button>
                @endif
            </x-stats.page-empty>
            @elseif(count($sessions) === 0)
            <div class="px-4 lg:px-6 py-3 mac-hairline-b">
                <x-inline-tip tone="amber">
                    Chưa có buổi {{ \App\Models\TeacherAttendanceSession::typeLabel((int) $attendanceType) }}.
                    @if($canCreateSessions)
                    Bấm vào <strong>tài khoản</strong> (góc dưới sidebar) →
                    <a href="{{ route('session.index', ['target' => 'teachers', 'namHoc' => $selectedNamHoc]) }}"
                        class="font-semibold underline hover:text-amber-950">Phiên điểm danh</a>
                    để tạo phiên, rồi quay lại trang này.
                    @else
                    Liên hệ Ban quản trị giáo lý để tạo phiên.
                    @endif
                </x-inline-tip>
            </div>
            <x-stats.page-empty
                :panel="false"
                title="Chưa có phiên điểm danh GLV"
                description="Tạo phiên tại menu tài khoản → Phiên điểm danh, rồi quay lại trang này để điểm."
                tone="primary">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </x-slot>
            </x-stats.page-empty>
            @else
                @if($this->viewMode !== 'mobile')
                <div class="overflow-x-auto overscroll-x-contain">
                    <table class="w-max min-w-full border-separate border-spacing-0">
                        <colgroup>
                            <col style="width: 3rem">
                            <col style="width: 7rem">
                            <col style="width: 11rem">
                            <col style="width: 9rem">
                        </colgroup>
                        <thead class="bg-slate-50 mac-hairline-b">
                            <tr>
                                <x-table-header class="static md:sticky md:left-0 z-[30] w-12 max-w-[3rem] shrink-0 !bg-slate-50">#</x-table-header>
                                <x-table-header class="static md:sticky md:left-[3rem] z-[31] w-28 max-w-[7rem] shrink-0 !bg-slate-50">Tên thánh</x-table-header>
                                <x-table-header class="sticky left-0 md:left-[10rem] z-[32] w-44 max-w-[11rem] shrink-0 !bg-slate-50 border-r border-black/[0.08] shadow-[4px_0_12px_-4px_rgba(0,0,0,0.12)] md:border-r-0 md:shadow-none">Họ và tên</x-table-header>
                                <x-table-header class="static md:sticky md:left-[21rem] z-[33] w-36 max-w-[9rem] shrink-0 !bg-slate-50 md:border-r md:border-black/[0.08] md:shadow-[4px_0_12px_-4px_rgba(0,0,0,0.12)]">Giáo họ</x-table-header>
                                @foreach($sessions as $session)
                                <x-table-header class="text-center min-w-[7.5rem] w-[7.5rem] shrink-0">
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
                                            type="button"
                                            x-on:click="markAll({{ $session['id'] }}, @js($teachers->pluck('id')->toArray()))"
                                            class="text-[9px] text-green-600 hover:text-green-700 hover:underline">
                                            ✓ Tất cả
                                        </button>
                                        @endif
                                    </div>
                                </x-table-header>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-black/[0.04]">
                            @foreach ($teachers as $index => $teacher)
                            <tr class="group hover:bg-black/[0.03] transition-colors"
                                wire:key="teacher-{{ $teacher->id }}">

                                <td class="px-4 py-3 text-sm text-slate-500 static md:sticky md:left-0 z-[20] w-12 max-w-[3rem] shrink-0 bg-white group-hover:bg-slate-50">
                                    {{ $index + 1 }}
                                </td>

                                <td class="px-4 py-3 text-sm text-slate-600 static md:sticky md:left-[3rem] z-[21] w-28 max-w-[7rem] shrink-0 bg-white group-hover:bg-slate-50">
                                    <span class="block truncate" title="{{ $teacher->saint?->name }}">
                                        {{ $teacher->saint?->name ?: '—' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 sticky left-0 md:left-[10rem] z-[22] w-44 max-w-[11rem] shrink-0 bg-white group-hover:bg-slate-50 border-r border-black/[0.08] shadow-[4px_0_12px_-4px_rgba(0,0,0,0.12)] md:border-r-0 md:shadow-none">
                                    <span class="block font-semibold text-slate-900 text-sm truncate" title="{{ $teacher->full_name }}">{{ $teacher->full_name }}</span>
                                </td>

                                <td class="px-4 py-3 text-sm text-slate-600 static md:sticky md:left-[21rem] z-[23] w-36 max-w-[9rem] shrink-0 bg-white group-hover:bg-slate-50 md:border-r md:border-black/[0.08] md:shadow-[4px_0_12px_-4px_rgba(0,0,0,0.12)]">
                                    <span class="line-clamp-2" title="{{ $teacher->parishGroup?->name }}">
                                        {{ $teacher->parishGroup?->name ?? '—' }}
                                    </span>
                                </td>

                                @foreach($sessions as $session)
                                <td class="px-3 py-3 text-center min-w-[7.5rem] w-[7.5rem] shrink-0"
                                    wire:key="tcell-{{ $teacher->id }}-{{ $session['id'] }}">

                                    @if($session['locked'])
                                    @php $status = $attendanceGrid[$teacher->id][$session['id']] ?? null; @endphp
                                    <div class="flex items-center justify-center h-8">
                                        @if((int) $status === 1)
                                        <span class="text-green-700 font-medium">✓</span>
                                        @elseif((int) $status === 2)
                                        <span class="text-yellow-700 font-medium">P</span>
                                        @elseif((int) $status === 3)
                                        <span class="text-red-700 font-medium">✕</span>
                                        @else
                                        <span class="text-xs text-slate-400">-</span>
                                        @endif
                                    </div>
                                    @else
                                    <div class="flex gap-1 justify-center">
                                        <button type="button"
                                            x-on:click="toggle({{ $teacher->id }}, {{ $session['id'] }}, 1)"
                                            :class="getStatus({{ $teacher->id }}, {{ $session['id'] }}) == 1
                                                    ? 'bg-green-500 text-white shadow-md scale-105'
                                                    : 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100'"
                                            class="px-2 py-1 rounded text-xs font-medium transition-all"
                                            aria-label="Có mặt">
                                            ✓
                                        </button>
                                        <div class="relative inline-block" x-data="{ open: false }">
                                            <button type="button"
                                                x-on:click="openNoteAlpine({{ $teacher->id }}, {{ $session['id'] }}, '{{ addslashes($teacher->full_name) }}')"
                                                @mouseenter="open = true"
                                                @mouseleave="open = false"
                                                :class="getStatus({{ $teacher->id }}, {{ $session['id'] }}) == 2
                                                ? 'bg-yellow-400 text-slate-900 shadow-md scale-105'
                                                : 'bg-amber-100 text-amber-800 border border-yellow-200 hover:bg-yellow-100'"
                                                class="px-2 py-1 rounded text-xs font-medium transition-all relative"
                                                aria-label="Vắng có phép">
                                                P
                                            </button>
                                            <div
                                                x-show="open && getNote({{ $teacher->id }}, {{ $session['id'] }})"
                                                x-transition x-cloak
                                                class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 w-48
                                                   p-2 bg-slate-900 text-white text-[10px] rounded-lg shadow-xl z-30">
                                                <div class="font-semibold mb-1">Lý do:</div>
                                                <div class="text-slate-200 line-clamp-2"
                                                    x-text="getNote({{ $teacher->id }}, {{ $session['id'] }})"></div>
                                            </div>
                                        </div>
                                        <button type="button"
                                            x-on:click="toggle({{ $teacher->id }}, {{ $session['id'] }}, 3)"
                                            :class="getStatus({{ $teacher->id }}, {{ $session['id'] }}) == 3
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

                            <tr class="bg-slate-50 font-semibold border-t-2 border-slate-300">
                                <td class="px-4 py-3 static md:sticky md:left-0 z-[24] w-12 max-w-[3rem] bg-slate-50"></td>
                                <td class="px-4 py-3 static md:sticky md:left-[3rem] z-[24] w-28 max-w-[7rem] bg-slate-50"></td>
                                <td class="px-4 py-3 text-sm text-slate-900 sticky left-0 md:left-[10rem] z-[24] w-44 max-w-[11rem] bg-slate-50 border-r border-black/[0.08] shadow-[4px_0_12px_-4px_rgba(0,0,0,0.12)] md:border-r-0 md:shadow-none">
                                    Thống kê
                                    <span class="block text-xs font-normal text-slate-500">({{ count($sessions) }} buổi — kéo ngang để xem thêm)</span>
                                </td>
                                <td class="px-4 py-3 static md:sticky md:left-[21rem] z-[24] w-36 max-w-[9rem] bg-slate-50 md:border-r md:border-black/[0.08] md:shadow-[4px_0_12px_-4px_rgba(0,0,0,0.12)]"></td>
                                @foreach($sessions as $session)
                                <td class="px-3 py-3 text-center min-w-[7.5rem] w-[7.5rem] shrink-0">
                                    <div class="flex flex-col gap-1 text-xs">
                                        <div class="text-green-600">✓ <span x-text="sessionStat({{ $session['id'] }}, 'present')"></span></div>
                                        <div class="text-yellow-600">P <span x-text="sessionStat({{ $session['id'] }}, 'absentPermitted')"></span></div>
                                        <div class="text-red-600">✕ <span x-text="sessionStat({{ $session['id'] }}, 'absentNotPermitted')"></span></div>
                                    </div>
                                </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
                @else
                {{-- Mobile / catechist: một ngày --}}
                <div>
                    @php
                    $currentSession = collect($sessions)->firstWhere('dateStr', $selectedDate);
                    $locked = $currentSession['locked'] ?? false;
                    $total = $teachers->count();
                    $mobileSessionId = $currentSession['id'] ?? null;
                    @endphp

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
                            @php $isActive = $session['dateStr'] === $selectedDate; @endphp
                            <button
                                type="button"
                                data-active="{{ $isActive ? 'true' : 'false' }}"
                                x-on:click="requestSelectDate('{{ $session['dateStr'] }}')"
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
                                <span class="text-[10px] leading-tight text-center {{ $isActive ? 'text-white/80' : 'text-slate-400' }}">
                                    {{ $session['dayName'] }}
                                </span>
                            </button>
                            @endforeach
                        </div>

                        @if($currentSession)
                        <div class="flex items-center justify-between px-4 py-2 bg-slate-50 border-t border-slate-100">
                            <div class="flex items-center gap-3 text-xs">
                                <span class="flex items-center gap-1 text-green-600 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                                    <span x-text="sessionStat({{ (int) $mobileSessionId }}, 'present')"></span>
                                </span>
                                <span class="flex items-center gap-1 text-yellow-600 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span>
                                    <span x-text="sessionStat({{ (int) $mobileSessionId }}, 'absentPermitted')"></span>
                                </span>
                                <span class="flex items-center gap-1 text-red-600 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                                    <span x-text="sessionStat({{ (int) $mobileSessionId }}, 'absentNotPermitted')"></span>
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
                            <button
                                type="button"
                                x-on:click="markAll({{ $currentSession['id'] }}, @js($teachers->pluck('id')->toArray()))"
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

                    <div class="overflow-x-auto">
                        <table class="w-full border-separate border-spacing-0">
                            <thead class="bg-slate-50/50 mac-hairline-b">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-full">
                                        Giáo lý viên
                                        <span class="ml-1 font-normal text-slate-400">({{ $teachers->count() }})</span>
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

                            <tbody class="divide-y divide-black/[0.04] bg-white">
                                @foreach ($teachers as $index => $teacher)
                                <tr wire:key="mobile-teacher-{{ $teacher->id }}-{{ $mobileSessionId }}"
                                    @if($mobileSessionId)
                                    :class="{
                                    'bg-green-50/40': getStatus({{ $teacher->id }}, {{ $mobileSessionId }}) == 1,
                                    'bg-red-50/30':   getStatus({{ $teacher->id }}, {{ $mobileSessionId }}) == 3,
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
                                                    {{ $teacher->saint?->name ?: '—' }}
                                                </div>
                                                <div class="text-sm font-semibold text-slate-900 leading-tight">
                                                    {{ $teacher->full_name }}
                                                </div>
                                                <div class="text-xs text-slate-500 mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                                    @if($teacher->parishGroup?->name)
                                                    <span class="truncate max-w-[160px]" title="{{ $teacher->parishGroup->name }}">
                                                        {{ $teacher->parishGroup->name }}
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-3 py-3" wire:key="mobile-tcell-{{ $teacher->id }}-{{ $mobileSessionId }}">
                                        @if($locked)
                                        @php
                                            $mobileStatus = $mobileSessionId
                                                ? ($attendanceGrid[$teacher->id][$mobileSessionId] ?? null)
                                                : null;
                                        @endphp
                                        <div class="flex justify-center">
                                            @if((int) $mobileStatus === 1)
                                            <span class="w-11 h-11 rounded-xl bg-green-500 flex items-center justify-center shadow-sm">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </span>
                                            @elseif((int) $mobileStatus === 2)
                                            <span class="w-11 h-11 rounded-xl bg-yellow-400 flex items-center justify-center shadow-sm">
                                                <span class="text-slate-900 font-bold text-base">P</span>
                                            </span>
                                            @elseif((int) $mobileStatus === 3)
                                            <span class="w-11 h-11 rounded-xl bg-red-500 flex items-center justify-center shadow-sm">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </span>
                                            @else
                                            <span class="w-11 h-11 flex items-center justify-center text-slate-300 text-lg">—</span>
                                            @endif
                                        </div>
                                        @elseif($mobileSessionId)
                                        <div class="flex gap-1.5 justify-center">
                                            <button type="button"
                                                x-on:click="toggle({{ $teacher->id }}, {{ $mobileSessionId }}, 1)"
                                                :class="getStatus({{ $teacher->id }}, {{ $mobileSessionId }}) == 1
                                                ? 'bg-green-500 text-white shadow-md ring-2 ring-green-300'
                                                : 'bg-green-50 text-green-700 border border-green-200 active:bg-green-100'"
                                                class="w-11 h-11 rounded-xl flex items-center justify-center transition-all active:scale-95"
                                                aria-label="Có mặt">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                            <button type="button"
                                                x-on:click="openNoteAlpine({{ $teacher->id }}, {{ $mobileSessionId }}, '{{ addslashes($teacher->full_name) }}')"
                                                :class="getStatus({{ $teacher->id }}, {{ $mobileSessionId }}) == 2
                                                ? 'bg-yellow-400 text-slate-900 shadow-md ring-2 ring-yellow-300'
                                                : 'bg-yellow-50 text-yellow-700 border border-yellow-200 active:bg-yellow-100'"
                                                class="w-11 h-11 rounded-xl flex items-center justify-center font-bold text-base transition-all active:scale-95"
                                                aria-label="Vắng có phép">
                                                P
                                            </button>
                                            <button type="button"
                                                x-on:click="toggle({{ $teacher->id }}, {{ $mobileSessionId }}, 3)"
                                                :class="getStatus({{ $teacher->id }}, {{ $mobileSessionId }}) == 3
                                                ? 'bg-red-500 text-white shadow-md ring-2 ring-red-300'
                                                : 'bg-red-50 text-red-700 border border-red-200 active:bg-red-100'"
                                                class="w-11 h-11 rounded-xl flex items-center justify-center transition-all active:scale-95"
                                                aria-label="Vắng không phép">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                    <div
                        x-show="!noteModal.open"
                        x-cloak
                        class="fixed left-0 right-0 z-20 bg-white/90 backdrop-blur border-t border-black/[0.06] px-4 py-3"
                        style="bottom: calc(env(safe-area-inset-bottom) + 60px);">
                        <div class="flex items-center gap-4 max-w-7xl mx-auto">
                            <x-button
                                variant="secondary"
                                size="sm"
                                class="flex-shrink-0"
                                x-on:click="discard()"
                                x-bind:disabled="isSaving || !hasDraft()">
                                Hủy
                            </x-button>

                            <x-button
                                variant="primary"
                                size="sm"
                                class="flex-1"
                                x-on:click="save()"
                                x-bind:disabled="isSaving || !hasDraft()">
                                <span x-text="saveButtonLabel()"></span>
                            </x-button>
                        </div>
                    </div>

                    <div class="h-24"></div>
                </div>
                @endif
            @endif

            @elseif (!$selectedClassId)
            <div class="px-4 lg:px-6 py-3 mac-hairline-b">
                <x-inline-tip>
                    Chọn <strong>năm học → khối → lớp</strong> (và học kỳ nếu có) ở bộ lọc phía trên để bắt đầu điểm danh.
                </x-inline-tip>
            </div>
            <x-stats.page-empty
                :panel="false"
                :title="'Vui lòng chọn lớp để bắt đầu điểm danh'"
                description="Chọn lớp ở bộ lọc phía trên để xem danh sách học sinh và các buổi điểm danh."
                tone="primary">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </x-slot>
            </x-stats.page-empty>

            @elseif ($students->isEmpty())
            <x-stats.page-empty
                :panel="false"
                :title="'Lớp chưa có học sinh'"
                description="Ghi danh học sinh vào lớp trước khi điểm danh."
                tone="primary">
                <x-slot name="icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </x-slot>
                @if(auth()->user()->canManage())
                <x-button as="a" href="{{ route('students.index', array_filter(['school-year' => $selectedNamHoc, 'class' => $selectedClassId])) }}" variant="primary">
                    <x-icon name="user-plus" />
                    Sang trang Học sinh
                </x-button>
                @endif
            </x-stats.page-empty>

            @else
                @if($students->count() > 0 && count($sessions) > 0)

                @if(!empty($unmarkedConclusiveSessions))
                @php
                    $unmarkedDates = collect($unmarkedConclusiveSessions)->pluck('date');
                    $unmarkedDateLabel = $unmarkedDates->take(5)->join(', ');
                    if ($unmarkedDates->count() > 5) {
                        $unmarkedDateLabel .= ', và ' . ($unmarkedDates->count() - 5) . ' buổi khác';
                    }
                @endphp
                <div class="px-4 lg:px-6 py-3 mac-hairline-b">
                    <x-inline-tip tone="amber">
                        <strong>Chưa có ai được điểm danh</strong> trong
                        {{ count($unmarkedConclusiveSessions) }} buổi đã khóa hoặc qua giờ chốt
                        ({{ $unmarkedDateLabel }}).
                        Hệ thống không tự tính cả lớp là KP để tránh trừ oan. Hãy kiểm tra và điểm danh ít nhất
                        một em, hoặc hủy buổi nếu lớp nghỉ.
                    </x-inline-tip>
                </div>
                @endif

                {{-- Bảng theo role: desktop = admin; mobile = catechist thuần — không theo breakpoint --}}
                @if($this->viewMode !== 'mobile')
                <div class="overflow-x-auto overscroll-x-contain">
                    <table class="w-max min-w-full border-separate border-spacing-0">
                        <colgroup>
                            <col style="width: 3rem">
                            <col style="width: 7rem">
                            <col style="width: 11rem">
                            <col style="width: 9rem">
                        </colgroup>
                        <thead class="bg-slate-50 mac-hairline-b">
                            <tr>
                                <x-table-header class="static md:sticky md:left-0 z-[30] w-12 max-w-[3rem] shrink-0 !bg-slate-50">#</x-table-header>
                                <x-table-header class="static md:sticky md:left-[3rem] z-[31] w-28 max-w-[7rem] shrink-0 !bg-slate-50">Tên thánh</x-table-header>
                                <x-table-header class="sticky left-0 md:left-[10rem] z-[32] w-44 max-w-[11rem] shrink-0 !bg-slate-50 border-r border-black/[0.08] shadow-[4px_0_12px_-4px_rgba(0,0,0,0.12)] md:border-r-0 md:shadow-none">Họ và tên</x-table-header>
                                <x-table-header class="static md:sticky md:left-[21rem] z-[33] w-36 max-w-[9rem] shrink-0 !bg-slate-50 md:border-r md:border-black/[0.08] md:shadow-[4px_0_12px_-4px_rgba(0,0,0,0.12)]">Giáo họ</x-table-header>
                                @foreach($sessions as $session)
                                <x-table-header class="text-center min-w-[7.5rem] w-[7.5rem] shrink-0">
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

                        <tbody class="divide-y divide-black/[0.04]">
                            @foreach ($students as $index => $student)
                            <tr class="group hover:bg-black/[0.03] transition-colors"
                                wire:key="student-{{ $student->id }}">

                                <td class="px-4 py-3 text-sm text-slate-500 static md:sticky md:left-0 z-[20] w-12 max-w-[3rem] shrink-0 bg-white group-hover:bg-slate-50">
                                    {{ $index + 1 }}
                                </td>

                                <td class="px-4 py-3 text-sm text-slate-600 static md:sticky md:left-[3rem] z-[21] w-28 max-w-[7rem] shrink-0 bg-white group-hover:bg-slate-50">
                                    <span class="block truncate" title="{{ $student->saint_name !== '-' ? $student->saint_name : '' }}">
                                        {{ $student->saint_name !== '-' ? $student->saint_name : '—' }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 sticky left-0 md:left-[10rem] z-[22] w-44 max-w-[11rem] shrink-0 bg-white group-hover:bg-slate-50 border-r border-black/[0.08] shadow-[4px_0_12px_-4px_rgba(0,0,0,0.12)] md:border-r-0 md:shadow-none">
                                    <span class="block font-semibold text-slate-900 text-sm truncate" title="{{ $student->full_name }}">{{ $student->full_name }}</span>
                                </td>

                                <td class="px-4 py-3 text-sm text-slate-600 static md:sticky md:left-[21rem] z-[23] w-36 max-w-[9rem] shrink-0 bg-white group-hover:bg-slate-50 md:border-r md:border-black/[0.08] md:shadow-[4px_0_12px_-4px_rgba(0,0,0,0.12)]">
                                    <span class="line-clamp-2" title="{{ $student->parishGroup?->name }}">
                                        {{ $student->parishGroup?->name ?? '—' }}
                                    </span>
                                </td>

                                @foreach($sessions as $session)
                                <td class="px-3 py-3 text-center min-w-[7.5rem] w-[7.5rem] shrink-0"
                                    wire:key="cell-{{ $student->id }}-{{ $session['id'] }}">

                                    @if($session['locked'])
                                    {{-- READ-ONLY: server data, không cần Alpine --}}
                                    @php
                                    $dbKey = $student->id . '_' . $session['id'];
                                    $dbStatus = $attendanceGrid[$student->id][$session['id']] ?? null;
                                    $dbNote = $attendanceRecords[$dbKey]['note'] ?? null;
                                    $dbInferred = (bool) ($attendanceRecords[$dbKey]['inferred'] ?? false);
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
                                        <span class="text-red-700 font-medium"
                                            title="{{ $dbInferred ? 'KP được suy luận sau giờ chốt' : 'Vắng không phép' }}">
                                            {{ $dbInferred ? 'KP' : '✕' }}
                                        </span>
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
                                                    : 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100'"
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
                                            :title="isInferred({{ $student->id }}, {{ $session['id'] }})
                                                ? 'KP được suy luận sau giờ chốt; có thể chọn trạng thái khác để sửa'
                                                : 'Vắng không phép'"
                                            aria-label="Vắng không phép"
                                            x-text="isInferred({{ $student->id }}, {{ $session['id'] }}) ? 'KP' : '✕'">
                                        </button>
                                    </div>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach

                            {{-- Stats Row — server pre-computed --}}
                            <tr class="bg-slate-50 font-semibold border-t-2 border-slate-300">
                                <td class="px-4 py-3 static md:sticky md:left-0 z-[24] w-12 max-w-[3rem] bg-slate-50"></td>
                                <td class="px-4 py-3 static md:sticky md:left-[3rem] z-[24] w-28 max-w-[7rem] bg-slate-50"></td>
                                <td class="px-4 py-3 text-sm text-slate-900 sticky left-0 md:left-[10rem] z-[24] w-44 max-w-[11rem] bg-slate-50 border-r border-black/[0.08] shadow-[4px_0_12px_-4px_rgba(0,0,0,0.12)] md:border-r-0 md:shadow-none">
                                    Thống kê
                                    <span class="block text-xs font-normal text-slate-500">({{ count($sessions) }} buổi — kéo ngang để xem thêm)</span>
                                </td>
                                <td class="px-4 py-3 static md:sticky md:left-[21rem] z-[24] w-36 max-w-[9rem] bg-slate-50 md:border-r md:border-black/[0.08] md:shadow-[4px_0_12px_-4px_rgba(0,0,0,0.12)]"></td>
                                @foreach($sessions as $session)
                                <td class="px-3 py-3 text-center min-w-[7.5rem] w-[7.5rem] shrink-0">
                                    <div class="flex flex-col gap-1 text-xs">
                                        <div class="text-green-600">✓ <span x-text="sessionStat({{ $session['id'] }}, 'present')"></span></div>
                                        <div class="text-yellow-600">P <span x-text="sessionStat({{ $session['id'] }}, 'absentPermitted')"></span></div>
                                        <div class="text-red-600">✕ <span x-text="sessionStat({{ $session['id'] }}, 'absentNotPermitted')"></span></div>
                                    </div>
                                </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
                @else
                {{-- Catechist / viewMode mobile: một ngày, sticky lưu --}}
                <div>
                    @php
                    $currentSession = collect($sessions)->firstWhere('dateStr', $selectedDate);
                    $locked = $currentSession['locked'] ?? false;
                    $total = $students->count();
                    $mobileSessionId = $currentSession['id'] ?? null;
                    @endphp

                    {{-- Date Selector Sticky — đủ phiên trong kỳ --}}
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
                            @endphp
                            <button
                                type="button"
                                data-active="{{ $isActive ? 'true' : 'false' }}"
                                x-on:click="requestSelectDate('{{ $session['dateStr'] }}')"
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
                                <span class="text-[10px] leading-tight text-center {{ $isActive ? 'text-white/80' : 'text-slate-400' }}">
                                    {{ $session['dayName'] }}
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
                                    <span x-text="sessionStat({{ (int) $mobileSessionId }}, 'present')"></span>
                                </span>
                                <span class="flex items-center gap-1 text-yellow-600 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span>
                                    <span x-text="sessionStat({{ (int) $mobileSessionId }}, 'absentPermitted')"></span>
                                </span>
                                <span class="flex items-center gap-1 text-red-600 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                                    <span x-text="sessionStat({{ (int) $mobileSessionId }}, 'absentNotPermitted')"></span>
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
                            <thead class="bg-slate-50/50 mac-hairline-b">
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

                            <tbody class="divide-y divide-black/[0.04] bg-white">
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
                                                <div class="text-xs text-slate-500 mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                                    @if($student->parishGroup?->name)
                                                        <span class="truncate max-w-[160px]" title="{{ $student->parishGroup->name }}">
                                                            {{ $student->parishGroup->name }}
                                                        </span>
                                                    @endif
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
                                        $mobileKey = $student->id . '_' . $mobileSessionId;
                                        $mobileInferred = (bool) ($attendanceRecords[$mobileKey]['inferred'] ?? false);
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
                                                @if($mobileInferred)
                                                <span class="text-white text-xs font-bold">KP</span>
                                                @else
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                @endif
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
                                                x-on:click="toggle({{ $student->id }}, {{ $mobileSessionId }}, 1)"
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
                                                :title="isInferred({{ $student->id }}, {{ $mobileSessionId }})
                                                    ? 'KP được suy luận sau giờ chốt; có thể chọn trạng thái khác để sửa'
                                                    : 'Vắng không phép'"
                                                aria-label="Vắng không phép">
                                                <span x-show="isInferred({{ $student->id }}, {{ $mobileSessionId }})"
                                                    class="text-xs font-bold">KP</span>
                                                <svg x-show="!isInferred({{ $student->id }}, {{ $mobileSessionId }})"
                                                    class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                    {{-- Sticky Lưu — luôn hiện trên mobile khi có bảng điểm danh --}}
                    <div
                        x-show="!noteModal.open"
                        x-cloak
                        class="fixed left-0 right-0 z-20 bg-white/90 backdrop-blur border-t border-black/[0.06] px-4 py-3"
                        style="bottom: calc(env(safe-area-inset-bottom) + 60px);">
                        <div class="flex items-center gap-4 max-w-7xl mx-auto">
                            <x-button
                                variant="secondary"
                                size="sm"
                                class="flex-shrink-0"
                                x-on:click="discard()"
                                x-bind:disabled="isSaving || !hasDraft()">
                                Hủy
                            </x-button>

                            <x-button
                                variant="primary"
                                size="sm"
                                class="flex-1"
                                x-on:click="save()"
                                x-bind:disabled="isSaving || !hasDraft()">
                                <span x-text="saveButtonLabel()"></span>
                            </x-button>
                        </div>
                    </div>

                    <div class="h-24"></div>
                </div>
                @endif

                @else
                    @if(empty($sessions))
                    <div class="px-4 lg:px-6 py-3 mac-hairline-b">
                        <x-inline-tip tone="amber">
                            Lớp đã chọn nhưng <strong>chưa có buổi điểm danh</strong>.
                            @if($canCreateSessions)
                                Vào
                                <a href="{{ route('session.index', array_filter([
                                    'namHoc' => $selectedNamHoc,
                                    'classId' => $selectedClassId,
                                    'khoi' => $selectedKhoi ?: null,
                                ])) }}" class="font-semibold underline hover:text-amber-950">Phiên điểm danh</a>
                                → <strong>Tạo phiên mới</strong>, rồi quay lại trang này.
                                <a href="{{ route('help.attendance') }}" class="font-semibold underline hover:text-amber-950 ml-1">Hướng dẫn →</a>
                            @else
                                Liên hệ quản trị viên để tạo buổi tại <strong>Phiên điểm danh</strong>, rồi tải lại trang này.
                            @endif
                        </x-inline-tip>
                    </div>
                    @endif
                    <x-stats.page-empty
                        :panel="false"
                        :title="empty($sessions) ? 'Chưa có buổi điểm danh nào' : 'Không có dữ liệu để hiển thị'"
                        :description="empty($sessions)
                            ? 'Tạo buổi điểm danh tại Phiên điểm danh, rồi quay lại trang này để điểm.'
                            : 'Vui lòng kiểm tra lại bộ lọc.'"
                        tone="primary">
                        <x-slot name="icon">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </x-slot>
                        @if(empty($sessions) && $canCreateSessions)
                        <div class="flex flex-wrap items-center justify-center gap-4">
                            <x-button as="a" href="{{ route('session.index', array_filter([
                                'namHoc' => $selectedNamHoc,
                                'classId' => $selectedClassId,
                                'khoi' => $selectedKhoi ?: null,
                            ])) }}" variant="primary">
                                <x-icon name="calendar" />
                                Quản lý phiên điểm danh
                            </x-button>
                            <x-button as="a" href="{{ route('help.attendance') }}" variant="secondary">
                                Xem hướng dẫn
                            </x-button>
                        </div>
                        @endif
                    </x-stats.page-empty>
                @endif
            @endif

        {{-- Note Modal — căn giữa viewport; không dùng bottom-sheet (tránh bị bottom-nav che) --}}
        <template x-teleport="body">
        <div
            x-show="noteModal.open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/40"
            style="padding-bottom: max(1rem, var(--bottom-offset, 0px)); padding-top: max(1rem, env(safe-area-inset-top, 0px));"
            x-on:click="noteModal.open = false"
            x-on:keydown.escape.window="if (noteModal.open) noteModal.open = false">

            <div
                x-show="noteModal.open"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="w-full max-w-[480px] rounded-2xl bg-white shadow-xl overflow-y-auto"
                style="max-height: calc(100dvh - var(--bottom-offset, 0px) - 2rem);"
                x-on:click.stop
                role="dialog"
                aria-modal="true"
                aria-label="Vắng có phép">

                <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-slate-200 sticky top-0 bg-white z-10">
                    <div class="min-w-0">
                        <h3 class="font-semibold text-slate-900">Vắng có phép</h3>
                        <p class="text-sm text-slate-500 mt-0.5 truncate" x-text="noteModal.studentName"></p>
                    </div>
                    <button
                        type="button"
                        x-on:click="noteModal.open = false"
                        class="flex-shrink-0 text-slate-400 hover:text-slate-600 transition-colors p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-4 sm:px-5 py-4 grid grid-cols-2 gap-2">
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

                <div class="px-4 sm:px-5 pb-5 flex gap-2">
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
        </template>
    </div>{{-- /alpine wire:key --}}

        </x-mac-panel>

        {{-- Modal xuất học sinh vắng — ngoài mac-panel --}}
        <div
            x-show="showAbsentExport"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="absent-export-title"
            @click="showAbsentExport = false; $wire.closeAbsentExportModal()"
            @keydown.escape.window="showAbsentExport = false; $wire.closeAbsentExportModal()">

            <div
                x-show="showAbsentExport"
                x-transition
                class="bg-white/90 backdrop-blur-xl rounded-2xl border border-black/[0.06] shadow-mac
                    w-full max-w-xl max-h-[90vh] overflow-hidden flex flex-col"
                @click.stop>

                <div class="flex-shrink-0 px-6 py-5 border-b border-black/[0.06]">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 id="absent-export-title" class="text-xl font-semibold tracking-tight text-slate-900">
                                {{ $subjectTarget === 'teachers' ? 'Xuất danh sách GLV vắng' : 'Xuất danh sách học sinh vắng' }}
                            </h2>
                            <p class="text-sm text-slate-500 mt-1">
                                @if($subjectTarget === 'teachers')
                                    Toàn giáo xứ · mỗi loại buổi một sheet
                                @else
                                    Toàn giáo xứ · sheet tổng hợp + chi tiết từng lớp
                                @endif
                                @if($selectedNamHoc)
                                · {{ \App\Models\NamHoc::find($selectedNamHoc)?->name }}
                                @endif
                            </p>
                        </div>
                        <button type="button"
                            @click="showAbsentExport = false; $wire.closeAbsentExportModal()"
                            class="flex-shrink-0 p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-black/[0.04] transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-5">
                    @if($errors->has('absentFromDate') || $errors->has('absentToDate') || $errors->has('absentExportType'))
                    <div class="bg-red-50/90 border border-red-200/80 rounded-xl p-4 shadow-mac-sm text-sm text-red-700">
                        {{ $errors->first('absentFromDate')
                            ?: ($errors->first('absentToDate') ?: $errors->first('absentExportType')) }}
                    </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                                Từ ngày <span class="text-red-500 normal-case">*</span>
                            </label>
                            <input type="date" wire:model.defer="absentFromDate"
                                class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 text-sm
                                    text-slate-900 shadow-mac-sm
                                    focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                                Đến ngày <span class="text-red-500 normal-case">*</span>
                            </label>
                            <input type="date" wire:model.defer="absentToDate"
                                class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] bg-white/80 text-sm
                                    text-slate-900 shadow-mac-sm
                                    focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-2 tracking-wide uppercase">
                            Loại buổi
                        </label>
                        @if($subjectTarget === 'teachers')
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <x-radio-card wire:model="absentExportType" :value="0" label="Tất cả" :checked="(int) $absentExportType === 0" />
                            <x-radio-card wire:model="absentExportType" :value="1" label="Đi dạy" :checked="(int) $absentExportType === 1" />
                            <x-radio-card wire:model="absentExportType" :value="2" label="Đi lễ" :checked="(int) $absentExportType === 2" />
                            <x-radio-card wire:model="absentExportType" :value="3" label="Họp" :checked="(int) $absentExportType === 3" />
                        </div>
                        @else
                        <div class="grid grid-cols-3 gap-3">
                            <x-radio-card wire:model="absentExportType" :value="0" label="Đi học và đi lễ" :checked="(int) $absentExportType === 0" />
                            <x-radio-card wire:model="absentExportType" :value="1" label="Đi học" :checked="(int) $absentExportType === 1" />
                            <x-radio-card wire:model="absentExportType" :value="2" label="Đi lễ" :checked="(int) $absentExportType === 2" />
                        </div>
                        @endif
                    </div>

                    <div class="bg-primary-50/80 border border-primary-200/60 rounded-xl p-4 shadow-mac-sm">
                        <ul class="text-sm text-primary-700 space-y-1">
                            @if($subjectTarget === 'teachers')
                            <li>• Xuất GLV vắng <strong>có phép và không phép</strong></li>
                            <li>• Mỗi loại buổi là <strong>một sheet</strong> (Đi dạy / Đi lễ / Họp)</li>
                            <li>• Ký hiệu: CP / KP / ?</li>
                            @else
                            <li>• Sheet đầu <strong>Tổng hợp</strong>: thống kê vắng theo từng lớp</li>
                            <li>• Các sheet sau: chi tiết học sinh vắng từng lớp (CP / KP / ?)</li>
                            <li>• Bao gồm cả vắng có phép và không phép</li>
                            @endif
                        </ul>
                    </div>
                </div>

                <div class="flex-shrink-0 px-6 py-4 border-t border-black/[0.06] bg-slate-50/70 flex justify-end gap-4">
                    <x-button type="button" variant="secondary"
                        @click="showAbsentExport = false; $wire.closeAbsentExportModal()">
                        Hủy
                    </x-button>
                    <x-button type="button" variant="primary"
                        wire:click="exportAbsentStudents"
                        wire:loading.attr="disabled"
                        wire:target="exportAbsentStudents">
                        <span wire:loading.remove wire:target="exportAbsentStudents" class="inline-flex items-center gap-2">
                            <x-icon name="file-export" />
                            Xuất Excel
                        </span>
                        <span wire:loading wire:target="exportAbsentStudents">Đang xuất…</span>
                    </x-button>
                </div>
            </div>
        </div>
    </div>{{-- /main-content --}}
</div>{{-- /min-h-screen --}}

@push('page-title')
<span class="text-slate-800 font-semibold text-sm">Điểm danh</span>
@endpush

@push('scripts')
<script>
    (function() {
        let collapsingObserver = null;

        function initCollapsingHeader() {
            const bigTitle = document.getElementById('page-big-title');
            const headerTitle = document.getElementById('header-collapsed-title');

            if (!bigTitle || !headerTitle) return;

            if (collapsingObserver) {
                collapsingObserver.disconnect();
                collapsingObserver = null;
            }

            collapsingObserver = new IntersectionObserver(
                ([entry]) => {
                    if (entry.isIntersecting) {
                        headerTitle.style.opacity = '0';
                        bigTitle.style.opacity = '1';
                    } else {
                        headerTitle.style.opacity = '1';
                        bigTitle.style.opacity = '0';
                    }
                }, {
                    threshold: 0,
                    rootMargin: '-56px 0px 0px 0px',
                }
            );

            collapsingObserver.observe(bigTitle);
        }

        document.addEventListener('livewire:load', initCollapsingHeader);
        document.addEventListener('livewire:update', initCollapsingHeader);
    })();

    document.addEventListener('livewire:load', function() {
        window.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const el = document.querySelector('[data-attendance-root]');
                if (el && window.Alpine) {
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