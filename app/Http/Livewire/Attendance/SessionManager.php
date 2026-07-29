<?php

namespace App\Http\Livewire\Attendance;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use App\Models\GradeLevel;
use App\Models\NamHoc;
use App\Models\ParishNew;
use App\Models\TeacherAttendanceSession;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Component quản lý Phiên điểm danh (CRUD)
 * Refactored theo chuẩn StudentListNew: pagination, queryString, property updaters.
 */
class SessionManager extends BaseComponent
{
    // ==================== TABS ====================

    /** sessions | settings */
    public string $activeTab = 'sessions';

    // ==================== FILTERS ====================

    /** students | teachers */
    public string $subjectTarget = 'students';

    /** @var int|null Selected năm học ID */
    public $selectedNamHoc = null;

    /** @var int|null Selected khối */
    public $selectedKhoi = null;

    /** @var int|null Selected lớp */
    public $selectedClassId = null;

    // ==================== FORM FIELDS ====================

    /** @var int|null ID session đang edit (null = create) */
    public $editingId = null;

    /** @var int Loại điểm danh (1: học, 2: lễ) */
    public $type = 1;

    /** @var string Tiêu đề phiên */
    public $title = '';

    /** @var string|null Ngày bắt đầu */
    public $startDate = null;

    /** @var string|null Ngày kết thúc */
    public $endDate = null;

    /** @var array Các ngày cụ thể được chọn */
    public $selectedDates = [];

    /** @var string Chế độ tạo: single, weekly, custom */
    public $createMode = 'single';

    /** @var array Các ngày trong tuần (0=CN … 6=T7) */
    public $weekDays = [];

    /** @var string|null Giờ bắt đầu */
    public $startTime = null;

    /** @var string|null Giờ kết thúc */
    public $endTime = null;

    // ==================== STATE ====================

    /** @var object|null Năm học hiện tại */
    public $currentNamHoc = null;

    // ==================== CÀI ĐẶT ĐIỂM DANH (cấp giáo xứ) ====================

    /** Bật/tắt việc tính học sinh chưa được điểm danh là vắng không phép sau giờ chốt. */
    public bool $autoFinalizeEnabled = true;

    /** Giờ chốt dạng H:i. */
    public string $autoFinalizeTime = ParishNew::DEFAULT_ATTENDANCE_AUTO_FINALIZE_TIME;

    // ==================== SORT ====================

    protected array $allowedSortFields = ['date', 'type', 'status'];

    public string $sortField    = 'date';
    public string $sortDirection = 'desc';

    // ==================== VALIDATION ====================

    protected $rules = [
        'selectedNamHoc'   => 'nullable|integer|exists:nam_hoc,id',
        'selectedKhoi'     => 'nullable|integer|exists:classes,grade_level_id',
        'selectedClassId'  => 'nullable|integer|exists:classes,id',
        'search'           => 'nullable|string|max:255',
        'perPage'          => 'required|integer|in:10,15,25,50,100',
        'type'             => 'required|integer|in:1,2',
        'title'            => 'nullable|string|max:255',
        'createMode'       => 'required|string|in:single,weekly,custom',
        'weekDays'         => 'required_if:createMode,weekly|array',
        'weekDays.*'       => 'integer|between:0,6',
        'selectedDates'    => 'required_if:createMode,custom|array',
        'startTime'        => 'nullable|date_format:H:i',
        'endTime'          => 'nullable|date_format:H:i|after:startTime',
    ];

    protected $messages = [
        'type.required'              => 'Vui lòng chọn loại điểm danh',
        'type.in'                    => 'Loại điểm danh không hợp lệ',
        'weekDays.required_if'       => 'Vui lòng chọn ít nhất 1 ngày trong tuần',
        'selectedDates.required_if'  => 'Vui lòng chọn ít nhất 1 ngày',
        'endTime.after'              => 'Giờ kết thúc phải sau giờ bắt đầu',
        'search.max'                 => 'Tìm kiếm không được quá 255 ký tự',
        'perPage.in'                 => 'Số mục trên trang không hợp lệ',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString(): array
    {
        return array_merge([
            'activeTab'       => ['as' => 'tab',     'except' => 'sessions'],
            'subjectTarget'   => ['as' => 'target',  'except' => 'students'],
            'selectedNamHoc'  => ['as' => 'namHoc',  'except' => null],
            'selectedKhoi'    => ['as' => 'khoi',    'except' => null],
            'selectedClassId' => ['as' => 'classId', 'except' => null],
            'sortField'       => ['as' => 'sort',    'except' => 'date'],
            'sortDirection'   => ['as' => 'dir',     'except' => 'desc'],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh'        => 'handleRefresh',
        'filterChanged'  => 'handleFilterChanged',
        'sessionCreated' => '$refresh',
        'sessionUpdated' => '$refresh',
    ];

    // ==================== LIFECYCLE ====================

    public function mount(): void
    {
        $this->authorize('viewAny', AttendanceSession::class);
        parent::mount();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        $this->subjectTarget = $this->subjectTarget === 'teachers' ? 'teachers' : 'students';
        $this->loadAttendanceSettings();

        if ($this->isTeachersTarget() && ! $this->canManageTeacherSessions()) {
            $this->subjectTarget = 'students';
        }

        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }

        if ($this->isTeachersTarget()) {
            $this->currentNamHoc = NamHoc::find($this->selectedNamHoc);

            return;
        }

        if (!$this->selectedClassId) {
            $this->selectedClassId = $this->defaultClassId
                ?? CatechismClass::where('school_year_id', $this->selectedNamHoc)
                ->orderBy('id')
                ->value('id');
        }

        if ($this->selectedClassId) {
            $this->loadClassInfo();
            $this->syncDateDefaults();
        }
    }

    // ==================== SANITIZE ====================

    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        $this->subjectTarget = $this->subjectTarget === 'teachers' ? 'teachers' : 'students';

        if (! in_array($this->activeTab, ['sessions', 'settings'], true)
            || ($this->activeTab === 'settings' && ! $this->canManageAttendanceSettings())
        ) {
            $this->activeTab = 'sessions';
        }

        $this->selectedNamHoc = is_numeric($this->selectedNamHoc)
            ? (int) $this->selectedNamHoc
            : null;

        $this->selectedKhoi = is_numeric($this->selectedKhoi)
            ? (int) $this->selectedKhoi
            : null;

        $this->selectedClassId = is_numeric($this->selectedClassId)
            ? (int) $this->selectedClassId
            : null;
    }

    protected function resetToDefaults(): void
    {
        parent::resetToDefaults();
        $this->selectedKhoi    = null;
        $this->selectedClassId = null;
    }

    // ==================== PROPERTY UPDATERS ====================

    public function updatedSearch(): void
    {
        $this->search = trim($this->search);

        try {
            $this->validateOnly('search');
        } catch (ValidationException $e) {
            $this->search = '';
            $this->emit('toast', 'warning', 'Từ khóa tìm kiếm không hợp lệ.');
        }

        $this->resetPage();
    }

    public function updatedSelectedNamHoc(): void
    {
        $this->selectedNamHoc = is_numeric($this->selectedNamHoc)
            ? (int) $this->selectedNamHoc
            : null;

        try {
            $this->validateOnly('selectedNamHoc');
        } catch (ValidationException $e) {
            $this->selectedNamHoc = null;
            $this->emit('toast', 'warning', 'Năm học không hợp lệ.');
        }

        $this->selectedKhoi    = null;
        $this->selectedClassId = null;
        $this->currentNamHoc   = $this->selectedNamHoc
            ? NamHoc::find($this->selectedNamHoc)
            : null;
        $this->search          = '';
        $this->resetPage();
    }

    public function switchSubjectTarget(string $target): void
    {
        $target = $target === 'teachers' ? 'teachers' : 'students';

        if ($target === 'teachers' && ! $this->canManageTeacherSessions()) {
            $this->emit('toast', 'error', 'Bạn không có quyền quản lý phiên điểm danh GLV');

            return;
        }

        if ($this->subjectTarget === $target) {
            return;
        }

        $this->subjectTarget = $target;
        $this->search = '';
        $this->type = 1;
        $this->resetPage();

        if ($this->isTeachersTarget()) {
            $this->currentNamHoc = NamHoc::find($this->selectedNamHoc);
        } elseif ($this->selectedClassId) {
            $this->loadClassInfo();
            $this->syncDateDefaults();
        }
    }

    public function switchTab(string $tab): void
    {
        $tab = in_array($tab, ['sessions', 'settings'], true) ? $tab : 'sessions';

        if ($tab === 'settings' && ! $this->canManageAttendanceSettings()) {
            $this->emit('toast', 'error', 'Bạn không có quyền thay đổi cài đặt điểm danh');

            return;
        }

        if ($this->activeTab === $tab) {
            return;
        }

        $this->resetErrorBag();
        $this->activeTab = $tab;

        if ($tab === 'settings') {
            $this->loadAttendanceSettings();
        }
    }

    // ==================== CÀI ĐẶT ĐIỂM DANH ====================

    protected function canManageAttendanceSettings(): bool
    {
        $user = auth()->user();

        return (bool) ($user && ($user->isParishAdmin() || $user->isCatechismAdmin()) && $this->parishId);
    }

    protected function loadAttendanceSettings(): void
    {
        $parish = $this->parishId ? ParishNew::query()->find($this->parishId) : null;

        $this->autoFinalizeEnabled = (bool) ($parish?->attendance_auto_finalize_enabled ?? true);
        $this->autoFinalizeTime = $parish?->attendanceAutoFinalizeTimeHi()
            ?? ParishNew::DEFAULT_ATTENDANCE_AUTO_FINALIZE_TIME;
    }

    public function saveAttendanceSettings(): void
    {
        if (! $this->canManageAttendanceSettings()) {
            $this->emit('toast', 'error', 'Bạn không có quyền thay đổi cài đặt điểm danh');

            return;
        }

        $validated = $this->validate([
            'autoFinalizeEnabled' => 'boolean',
            'autoFinalizeTime'    => ['required', 'regex:/^\d{1,2}:\d{2}$/'],
        ], [
            'autoFinalizeTime.required' => 'Vui lòng chọn giờ chốt số liệu.',
            'autoFinalizeTime.regex'    => 'Giờ chốt không hợp lệ (định dạng HH:MM).',
        ]);

        [$hour, $minute] = array_map('intval', explode(':', $validated['autoFinalizeTime']));

        if ($hour > 23 || $minute > 59) {
            $this->addError('autoFinalizeTime', 'Giờ chốt không hợp lệ.');

            return;
        }

        try {
            ParishNew::query()->findOrFail($this->parishId)->update([
                'attendance_auto_finalize_enabled' => (bool) $validated['autoFinalizeEnabled'],
                'attendance_auto_finalize_time'    => sprintf('%02d:%02d:00', $hour, $minute),
            ]);

            $this->loadAttendanceSettings();
            $this->emit('toast', 'success', 'Đã lưu cài đặt điểm danh');
        } catch (\Exception $e) {
            $this->logError($e, 'Error saving attendance settings', [
                'parishId' => $this->parishId,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi lưu cài đặt điểm danh');
        }
    }

    protected function isTeachersTarget(): bool
    {
        return $this->subjectTarget === 'teachers';
    }

    /**
     * Tạo / khóa buổi điểm danh GLV: cùng quyền tạo phiên học sinh
     * (quản trị giáo lý hoặc GLV có create_attendance_sessions + phân công).
     * Xóa buổi GLV vẫn chỉ dành cho quản trị — xem deleteTeacherSession().
     */
    protected function canManageTeacherSessions(): bool
    {
        return (bool) auth()->user()?->canCreateAttendanceSessions(
            $this->parishId ? (int) $this->parishId : null
        );
    }

    public function updatedSelectedKhoi(): void
    {
        $this->selectedKhoi = is_numeric($this->selectedKhoi)
            ? (int) $this->selectedKhoi
            : null;

        if ($this->selectedKhoi) {
            try {
                $this->validateOnly('selectedKhoi');
            } catch (ValidationException $e) {
                $this->selectedKhoi = null;
                $this->emit('toast', 'warning', 'Khối không hợp lệ.');
            }
        }

        $this->selectedClassId = null;
        $this->currentNamHoc   = null;
        $this->resetPage();
    }

    public function updatedSelectedClassId(): void
    {
        $this->selectedClassId = is_numeric($this->selectedClassId)
            ? (int) $this->selectedClassId
            : null;

        if ($this->selectedClassId) {
            try {
                $this->validateOnly('selectedClassId');
            } catch (ValidationException $e) {
                $this->selectedClassId = null;
                $this->emit('toast', 'warning', 'Lớp không hợp lệ.');
                return;
            }

            $this->loadClassInfo();
            $this->syncDateDefaults();
        } else {
            $this->currentNamHoc = null;
        }

        $this->resetPage();
    }

    // ==================== DATA LOADING ====================

    protected function loadClassInfo(): void
    {
        if (!$this->selectedClassId) {
            return;
        }

        try {
            $class = CatechismClass::with(['gradeLevel', 'schoolYear'])
                ->findOrFail($this->selectedClassId);

            $this->selectedNamHoc = $class->school_year_id;
            $this->currentNamHoc  = $class->schoolYear;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading class info');
            $this->emit('toast', 'error', 'Không tìm thấy lớp học');
        }
    }

    protected function syncDateDefaults(): void
    {
        if (!$this->currentNamHoc) {
            return;
        }

        $this->startDate = $this->currentNamHoc->start_date_one
            ? $this->currentNamHoc->start_date_one->format('Y-m-d')
            : null;

        $this->endDate = $this->currentNamHoc->end_date_one
            ? $this->currentNamHoc->end_date_one->format('Y-m-d')
            : null;
    }

    // ==================== QUERY HELPERS ====================

    /**
     * Base query dùng chung cho cả paginate lẫn count / stats.
     */
    protected function getCurrentSessionsQuery()
    {
        if ($this->isTeachersTarget()) {
            return $this->getTeacherSessionsQuery();
        }

        $query = AttendanceSession::query();

        if ($this->selectedClassId) {
            $query->where('class_id', $this->selectedClassId);
        } elseif ($this->selectedNamHoc) {
            // Nếu không chọn lớp cụ thể, lọc theo tất cả lớp trong năm học
            $classIds = CatechismClass::where('school_year_id', $this->selectedNamHoc)
                ->when($this->selectedKhoi, fn($q) => $q->where('grade_level_id', $this->selectedKhoi))
                ->pluck('id');

            $query->whereIn('class_id', $classIds);
        }

        if (!empty(trim($this->search))) {
            $query->searchByDate($this->search);
        }

        $this->applySorting($query);

        return $query;
    }

    protected function getTeacherSessionsQuery()
    {
        $query = TeacherAttendanceSession::query()
            ->with('records')
            ->where('parish_id', $this->parishId);

        if ($this->selectedNamHoc) {
            $query->where('namhoc_id', (int) $this->selectedNamHoc);
        } else {
            $query->whereRaw('1 = 0');
        }

        $search = trim((string) $this->search);
        if ($search !== '') {
            try {
                $parsed = Carbon::createFromFormat('d/m/Y', $search);
                if ($parsed) {
                    $query->whereDate('date', $parsed->toDateString());
                }
            } catch (\Exception $e) {
                // ignore invalid search
            }
        }

        $this->applySorting($query);

        return $query;
    }

    protected function getSessionsPaginated(): LengthAwarePaginator
    {
        try {
            return $this->getCurrentSessionsQuery()
                ->paginate($this->perPage);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading sessions', [
                'target'  => $this->subjectTarget,
                'namhoc'  => $this->selectedNamHoc,
                'classId' => $this->selectedClassId,
                'search'  => $this->search,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi tải danh sách phiên điểm danh.');
            return new LengthAwarePaginator([], 0, $this->perPage, $this->page ?? 1);
        }
    }

    // ==================== SESSION STATS HELPER ====================

    /**
     * Map một AttendanceSession model sang array hiển thị (dùng trong blade).
     */
    protected function mapSession(AttendanceSession $session): array
    {
        $stats = $session->getStatistics();

        return [
            'id'          => $session->id,
            'dateStr'     => $session->date->format('Y-m-d'),
            'fullDate'    => $session->date->format('d/m/Y'),
            'dayName'     => $this->getVietnameseDayName($session->date),
            'type'        => $session->type,
            'typeLabel'   => $session->type == AttendanceSession::TYPE_CLASS ? 'Đi học' : 'Đi lễ',
            'title'       => $session->title ?? $session->note,
            'status'      => $session->status,
            'statusLabel' => AttendanceSession::statusLabel((int) $session->status),
            'statusClass' => AttendanceSession::statusClass((int) $session->status),
            'locked'      => (int) $session->status === AttendanceSession::STATUS_CLOSED,
            'cancelled'   => (int) $session->status === AttendanceSession::STATUS_CANCELLED,
            'canLock'     => (int) $session->status === AttendanceSession::STATUS_OPENING,
            'canReopen'   => (int) $session->status === AttendanceSession::STATUS_CLOSED,
            'canCancel'   => in_array((int) $session->status, [
                AttendanceSession::STATUS_OPENING,
                AttendanceSession::STATUS_CLOSED,
            ], true),
            'canRestore'  => (int) $session->status === AttendanceSession::STATUS_CANCELLED,
            'start_time'  => $session->start_time?->format('H:i'),
            'end_time'    => $session->end_time?->format('H:i'),
            'stats'       => $stats,
        ];
    }

    protected function mapTeacherSession(TeacherAttendanceSession $session): array
    {
        $stats = $session->getStatistics();
        $status = (int) $session->status;

        return [
            'id'          => $session->id,
            'dateStr'     => $session->date->format('Y-m-d'),
            'fullDate'    => $session->date->format('d/m/Y'),
            'dayName'     => $this->getVietnameseDayName($session->date),
            'type'        => $session->type,
            'typeLabel'   => TeacherAttendanceSession::typeLabel((int) $session->type),
            'title'       => $session->note,
            'status'      => $status,
            'statusLabel' => TeacherAttendanceSession::statusLabel($status),
            'statusClass' => TeacherAttendanceSession::statusClass($status),
            'locked'      => $status === TeacherAttendanceSession::STATUS_CLOSED,
            'cancelled'   => $status === TeacherAttendanceSession::STATUS_CANCELLED,
            'canLock'     => $status === TeacherAttendanceSession::STATUS_OPENING,
            'canReopen'   => $status === TeacherAttendanceSession::STATUS_CLOSED,
            'canCancel'   => in_array($status, [
                TeacherAttendanceSession::STATUS_OPENING,
                TeacherAttendanceSession::STATUS_CLOSED,
            ], true),
            'canRestore'  => $status === TeacherAttendanceSession::STATUS_CANCELLED,
            'start_time'  => $session->start_time?->format('H:i'),
            'end_time'    => $session->end_time?->format('H:i'),
            'stats'       => $stats,
        ];
    }

    // ==================== EVENT HANDLERS ====================

    public function handleFilterChanged(array $filters): void
    {
        if (!is_array($filters)) {
            return;
        }

        if (array_key_exists('namHoc', $filters)) {
            $newNamHoc = is_numeric($filters['namHoc']) ? (int) $filters['namHoc'] : null;
            if ($newNamHoc !== $this->selectedNamHoc) {
                $this->selectedNamHoc  = $newNamHoc;
                $this->selectedKhoi    = null;
                $this->selectedClassId = null;
                $this->currentNamHoc   = $newNamHoc ? NamHoc::find($newNamHoc) : null;
                $this->search          = '';
            }
        }

        if (array_key_exists('khoi', $filters)) {
            $newKhoi = is_numeric($filters['khoi']) ? (int) $filters['khoi'] : null;
            if ($newKhoi !== $this->selectedKhoi) {
                $this->selectedKhoi    = $newKhoi;
                $this->selectedClassId = null;
                $this->currentNamHoc   = null;
            }
        }

        if (array_key_exists('lop', $filters)) {
            $newClassId = is_numeric($filters['lop']) ? (int) $filters['lop'] : null;
            if ($newClassId !== $this->selectedClassId) {
                $this->selectedClassId = $newClassId;
                if ($this->selectedClassId) {
                    $this->loadClassInfo();
                    $this->syncDateDefaults();
                } else {
                    $this->currentNamHoc = null;
                }
            }
        }

        $this->resetPage();
    }

    public function handleRefresh(): void
    {
        $this->resetPage();
    }

    // ==================== CRUD ACTIONS ====================

    public function create(): void
    {
        if ($this->isTeachersTarget()) {
            if (! $this->canManageTeacherSessions()) {
                $this->emit('toast', 'error', 'Bạn không có quyền tạo buổi điểm danh GLV');

                return;
            }
        } else {
            $this->authorize('create', AttendanceSession::class);
        }

        $this->currentNamHoc = NamHoc::find($this->selectedNamHoc);

        if (!$this->currentNamHoc) {
            $this->emit('toast', 'warning', 'Không tìm thấy thông tin năm học');
            return;
        }

        $this->resetForm();

        $today           = Carbon::today();
        $currentSemester = $this->getCurrentSemester();

        if ($currentSemester === 1 && $this->currentNamHoc->start_date_one) {
            $this->startDate = $today->max($this->currentNamHoc->start_date_one)->format('Y-m-d');
            $this->endDate   = $this->currentNamHoc->end_date_one?->format('Y-m-d');
        } elseif ($currentSemester === 2 && $this->currentNamHoc->start_date_two) {
            $this->startDate = $today->max($this->currentNamHoc->start_date_two)->format('Y-m-d');
            $this->endDate   = $this->currentNamHoc->end_date_two?->format('Y-m-d');
        } else {
            $this->startDate = $today->format('Y-m-d');
        }

        $this->emit('openModal');
    }

    public function save(): void
    {
        if ($this->isTeachersTarget()) {
            $this->saveTeacherSessions();

            return;
        }

        $this->authorize('create', AttendanceSession::class);

        if ($this->createMode === 'weekly' && empty($this->weekDays)) {
            $this->emit('toast', 'error', 'Vui lòng chọn ít nhất 1 ngày trong tuần.');
            return;
        }

        if ($this->createMode === 'custom' && empty($this->selectedDates)) {
            $this->emit('toast', 'error', 'Vui lòng chọn ít nhất 1 ngày.');
            return;
        }

        if (in_array($this->createMode, ['single', 'weekly'], true) && !$this->startDate) {
            $this->emit('toast', 'error', 'Vui lòng chọn ngày bắt đầu.');
            return;
        }

        $this->validate($this->rules, $this->messages);

        try {
            DB::beginTransaction();

            $classIds = $this->resolveClassIds();
            $dates    = $this->generateDates();

            if (empty($classIds)) {
                $this->emit('toast', 'warning', 'Không tìm thấy lớp nào trong phạm vi đã chọn');
                DB::rollBack();
                return;
            }

            if (empty($dates)) {
                $this->emit('toast', 'warning', 'Không có ngày nào được tạo. Vui lòng kiểm tra lại.');
                DB::rollBack();
                return;
            }

            $created = 0;
            $skipped = 0;

            foreach ($classIds as $classId) {
                foreach ($dates as $date) {
                    $exists = AttendanceSession::where('class_id', $classId)
                        ->where('type', $this->type)
                        ->whereDate('date', $date)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    $semester = $this->getSemesterForDate($date);

                    try {
                        AttendanceSession::create([
                            'class_id'   => $classId,
                            'date'       => $date,
                            'semester'   => $semester,
                            'type'       => $this->type,
                            'status'     => AttendanceSession::STATUS_OPENING,
                            'start_time' => $this->startTime ? Carbon::parse($this->startTime) : null,
                            'end_time'   => $this->endTime   ? Carbon::parse($this->endTime)   : null,
                            'note'       => $this->title ?: null,
                        ]);
                        $created++;
                    } catch (\Exception $e) {
                        $this->logError($e, 'Error creating attendance session', [
                            'class_id' => $classId,
                            'date'     => $date,
                        ]);
                    }
                }
            }

            DB::commit();

            $message = "Đã tạo {$created} phiên điểm danh";
            if ($skipped > 0) {
                $message .= " ({$skipped} phiên đã tồn tại, bỏ qua)";
            }

            $this->emit('toast', 'success', $message);
            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error creating sessions', [
                'class_id' => $this->selectedClassId,
                'mode'     => $this->createMode,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi tạo phiên điểm danh. Vui lòng thử lại.');
        }
    }

    protected function saveTeacherSessions(): void
    {
        if (! $this->canManageTeacherSessions()) {
            $this->emit('toast', 'error', 'Bạn không có quyền tạo buổi điểm danh GLV');

            return;
        }

        if (! $this->parishId || ! $this->selectedNamHoc) {
            $this->emit('toast', 'warning', 'Vui lòng chọn năm học');

            return;
        }

        if ($this->createMode === 'weekly' && empty($this->weekDays)) {
            $this->emit('toast', 'error', 'Vui lòng chọn ít nhất 1 ngày trong tuần.');

            return;
        }

        if ($this->createMode === 'custom' && empty($this->selectedDates)) {
            $this->emit('toast', 'error', 'Vui lòng chọn ít nhất 1 ngày.');

            return;
        }

        if (in_array($this->createMode, ['single', 'weekly'], true) && ! $this->startDate) {
            $this->emit('toast', 'error', 'Vui lòng chọn ngày bắt đầu.');

            return;
        }

        $rules = $this->rules;
        $rules['type'] = 'required|integer|in:1,2,3';
        $this->validate($rules, $this->messages);

        try {
            DB::beginTransaction();

            $dates = $this->generateDates();

            if (empty($dates)) {
                $this->emit('toast', 'warning', 'Không có ngày nào được tạo. Vui lòng kiểm tra lại.');
                DB::rollBack();

                return;
            }

            $created = 0;
            $skipped = 0;

            foreach ($dates as $date) {
                $exists = TeacherAttendanceSession::query()
                    ->where('parish_id', $this->parishId)
                    ->where('namhoc_id', (int) $this->selectedNamHoc)
                    ->where('type', (int) $this->type)
                    ->whereDate('date', $date)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                TeacherAttendanceSession::create([
                    'parish_id'  => $this->parishId,
                    'namhoc_id'  => (int) $this->selectedNamHoc,
                    'date'       => $date,
                    'type'       => (int) $this->type,
                    'status'     => TeacherAttendanceSession::STATUS_OPENING,
                    'start_time' => $this->startTime ? Carbon::parse($this->startTime) : null,
                    'end_time'   => $this->endTime ? Carbon::parse($this->endTime) : null,
                    'note'       => $this->title ?: null,
                ]);
                $created++;
            }

            DB::commit();

            $message = "Đã tạo {$created} buổi điểm danh GLV";
            if ($skipped > 0) {
                $message .= " ({$skipped} buổi đã tồn tại, bỏ qua)";
            }

            $this->emit('toast', 'success', $message);
            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error creating teacher sessions', [
                'namhoc' => $this->selectedNamHoc,
                'mode'   => $this->createMode,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi tạo buổi điểm danh GLV. Vui lòng thử lại.');
        }
    }

    public function lockSession(int $id): void
    {
        $this->changeSessionStatus($id, 'lock');
    }

    public function reopenSession(int $id): void
    {
        $this->changeSessionStatus($id, 'reopen');
    }

    public function cancelSession(int $id): void
    {
        $this->changeSessionStatus($id, 'cancel');
    }

    public function restoreSession(int $id): void
    {
        $this->changeSessionStatus($id, 'restore');
    }

    protected function changeSessionStatus(int $id, string $action): void
    {
        if ($this->isTeachersTarget()) {
            $this->changeTeacherSessionStatus($id, $action);

            return;
        }

        try {
            $session = AttendanceSession::findOrFail($id);
            $this->authorize('update', $session);

            $ok = match ($action) {
                'lock'    => $session->close(),
                'reopen'  => $session->reopen(),
                'cancel'  => $session->cancel(),
                'restore' => $session->restore(),
                default   => false,
            };

            if (! $ok) {
                $this->emit('toast', 'error', 'Không thể chuyển trạng thái phiên này');

                return;
            }

            $this->emit('toast', 'success', match ($action) {
                'lock'    => 'Đã khóa phiên điểm danh',
                'reopen'  => 'Đã mở lại phiên điểm danh',
                'cancel'  => 'Đã hủy phiên điểm danh',
                'restore' => 'Đã khôi phục phiên điểm danh',
                default   => 'Đã cập nhật trạng thái',
            });
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->emit('toast', 'error', 'Bạn không có quyền thay đổi trạng thái phiên');
        } catch (\Exception $e) {
            $this->logError($e, 'Error changing session status', [
                'id'     => $id,
                'action' => $action,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi thay đổi trạng thái');
        }
    }

    protected function changeTeacherSessionStatus(int $id, string $action): void
    {
        if (! $this->canManageTeacherSessions()) {
            $this->emit('toast', 'error', 'Bạn không có quyền thay đổi trạng thái buổi GLV');

            return;
        }

        try {
            $session = TeacherAttendanceSession::query()
                ->where('parish_id', $this->parishId)
                ->findOrFail($id);

            $ok = match ($action) {
                'lock'    => $session->close(),
                'reopen'  => $session->reopen(),
                'cancel'  => $session->cancel(),
                'restore' => $session->restore(),
                default   => false,
            };

            if (! $ok) {
                $this->emit('toast', 'error', 'Không thể chuyển trạng thái buổi GLV này');

                return;
            }

            $this->emit('toast', 'success', match ($action) {
                'lock'    => 'Đã khóa buổi điểm danh GLV',
                'reopen'  => 'Đã mở lại buổi điểm danh GLV',
                'cancel'  => 'Đã hủy buổi điểm danh GLV',
                'restore' => 'Đã khôi phục buổi điểm danh GLV',
                default   => 'Đã cập nhật trạng thái',
            });
        } catch (\Exception $e) {
            $this->logError($e, 'Error changing teacher session status', [
                'id'     => $id,
                'action' => $action,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi thay đổi trạng thái');
        }
    }

    public function delete(int $id): void
    {
        if ($this->isTeachersTarget()) {
            $this->deleteTeacherSession($id);

            return;
        }

        try {
            $session = AttendanceSession::findOrFail($id);
            $this->authorize('delete', $session);

            if ($session->records()->whereNotNull('status')->exists()) {
                $this->emit('toast', 'error', 'Không thể xóa phiên đã có dữ liệu điểm danh');
                return;
            }

            DB::beginTransaction();
            $session->delete();
            DB::commit();

            $this->emit('toast', 'success', 'Đã xóa phiên điểm danh');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->emit('toast', 'error', 'Bạn không có quyền xóa phiên điểm danh');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting session', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa phiên điểm danh');
        }
    }

    protected function deleteTeacherSession(int $id): void
    {
        if (! auth()->user()?->canManageCatechism()) {
            $this->emit('toast', 'error', 'Bạn không có quyền xóa buổi điểm danh GLV');

            return;
        }

        try {
            $session = TeacherAttendanceSession::query()
                ->where('parish_id', $this->parishId)
                ->findOrFail($id);

            if ($session->records()->whereNotNull('status')->exists()) {
                $this->emit('toast', 'error', 'Không thể xóa buổi đã có dữ liệu điểm danh');

                return;
            }

            DB::beginTransaction();
            $session->delete();
            DB::commit();

            $this->emit('toast', 'success', 'Đã xóa buổi điểm danh GLV');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting teacher session', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa buổi điểm danh GLV');
        }
    }

    // ==================== MODAL ====================

    public function addSelectedDate(): void
    {
        if (!$this->startDate) {
            $this->emit('toast', 'warning', 'Vui lòng chọn ngày trước khi thêm.');
            return;
        }

        if (in_array($this->startDate, $this->selectedDates, true)) {
            $this->emit('toast', 'info', 'Ngày này đã có trong danh sách.');
            return;
        }

        $this->selectedDates[] = $this->startDate;
        sort($this->selectedDates);
        $this->selectedDates = array_values($this->selectedDates);
    }

    public function removeSelectedDate(string $date): void
    {
        $this->selectedDates = array_values(array_filter(
            $this->selectedDates,
            fn ($d) => $d !== $date
        ));
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->resetValidation();
        $this->emit('closeModal');
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingId',
            'type',
            'title',
            'startDate',
            'endDate',
            'selectedDates',
            'createMode',
            'weekDays',
            'startTime',
            'endTime',
        ]);

        $this->type       = 1;
        $this->createMode = 'single';
        $this->resetValidation();
    }

    // ==================== SCOPE / CLASS RESOLUTION ====================

    protected function resolveClassIds(): array
    {
        if (!$this->selectedNamHoc) {
            return [];
        }

        $query = CatechismClass::where('school_year_id', $this->selectedNamHoc);

        if ($this->selectedClassId !== null) {
            return [$this->selectedClassId];
        }

        if ($this->selectedKhoi !== null) {
            return (clone $query)
                ->where('grade_level_id', $this->selectedKhoi)
                ->pluck('id')
                ->toArray();
        }

        return (clone $query)->pluck('id')->toArray();
    }

    // ==================== DATE HELPERS ====================

    protected function generateDates(): array
    {
        $dates = [];

        switch ($this->createMode) {
            case 'single':
                if ($this->startDate) {
                    $dates[] = $this->startDate;
                }
                break;

            case 'weekly':
                $start = Carbon::parse($this->startDate);
                $end   = $this->endDate
                    ? Carbon::parse($this->endDate)
                    : $start->copy()->addMonths(3);

                while ($start <= $end) {
                    if (in_array($start->dayOfWeek, array_map('intval', $this->weekDays))) {
                        $dates[] = $start->format('Y-m-d');
                    }
                    $start->addDay();
                }
                break;

            case 'custom':
                $dates = $this->selectedDates;
                break;
        }

        return array_unique($dates);
    }

    protected function getSemesterForDate(string|Carbon $date): ?int
    {
        if (! $this->currentNamHoc) {
            return null;
        }

        return app(\App\Services\SchoolYearResolver::class)
            ->semesterForDate($this->currentNamHoc, $date);
    }

    protected function getCurrentSemester(): ?int
    {
        return $this->getSemesterForDate(Carbon::today());
    }

    // ==================== LOOKUP HELPERS ====================

    protected function getDefaultNamHocId(): ?int
    {
        return app(\App\Services\SchoolYearResolver::class)
            ->resolveId($this->parishId ? (int) $this->parishId : null);
    }

    protected function getVietnameseDayName(Carbon $date): string
    {
        return ['Chúa Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'][$date->dayOfWeek];
    }

    // ==================== COMPUTED PROPERTIES ====================

    public function getSelectedClassNameProperty(): string
    {
        if (!$this->selectedClassId) {
            return 'Chọn lớp';
        }

        return CatechismClass::where('id', $this->selectedClassId)->value('name') ?? 'Chọn lớp';
    }

    public function getSelectedKhoiNameProperty(): string
    {
        if (!$this->selectedKhoi) {
            return 'Chọn khối';
        }

        return GradeLevel::where('id', $this->selectedKhoi)->value('name') ?? 'Chọn khối';
    }

    // ==================== RENDER ====================

    public function render()
    {
        if ($this->isTeachersTarget() && ! $this->currentNamHoc && $this->selectedNamHoc) {
            $this->currentNamHoc = NamHoc::find($this->selectedNamHoc);
        }

        $paginator = $this->getSessionsPaginated();

        $sessions = $paginator->through(fn ($session) => $this->isTeachersTarget()
            ? $this->mapTeacherSession($session)
            : $this->mapSession($session)
        );

        return view('livewire.attendance.session-manager', [
            'parishId'  => $this->parishId,
            'sessions'  => $sessions,
            'total'     => $paginator->total(),
            'canDeleteSessions' => (bool) auth()->user()?->canManageCatechism(),
            'isMobileUi' => (bool) auth()->user()?->usesCatechistLayout(),
            'canManageParishSettings' => $this->canManageAttendanceSettings(),
        ])
            ->extends(auth()->user()?->usesCatechistLayout()
                ? 'frontend.layout.catechist'
                : 'frontend.layout.main')
            ->section('content');
    }
}
