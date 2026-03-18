<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use App\Models\NamHoc;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceManager extends BaseComponent
{
    protected AttendanceService $attendanceService;

    // ==================== FILTERS ====================

    public $selectedNamHoc  = null;
    public $selectedKhoi    = '';
    public $selectedClassId = null;
    public $selectedKy      = '';
    public $attendanceType  = 1;
    public $filterStatus    = 'all';

    // ==================== VIEW STATE ====================

    public $viewMode    = 'desktop';
    public $selectedDate = null;

    // ==================== NOTE MODAL ====================
    // Vẫn giữ modal phía Livewire vì cần load student name từ server

    public $showNoteModal     = false;
    public $currentStudentId  = null;
    public $currentSessionId  = null;
    public $currentStudentName = '';
    public $attendanceNote    = '';

    // ==================== DATA ====================

    protected $students;
    public $sessions         = [];
    public $attendanceRecords = [];  // load từ DB, đọc bởi Alpine
    public $sessionHasRecord  = [];  // cho mobile chip dots

    // ✅ BỎ $draftAttendance — draft sống trong Alpine

    // ==================== CLASS NAME ====================

    public string $selectedClassName = '';

    // ==================== VALIDATION ====================

    protected $rules = [
        'selectedClassId' => 'nullable|integer|exists:classes,id',
        'attendanceType'  => 'required|integer|in:1,2',
        'filterStatus'    => 'required|string|in:all,present,absent',
        'search'          => 'nullable|string|max:255',
        'attendanceNote'  => 'nullable|string|max:500',
    ];

    protected $messages = [
        'selectedClassId.exists' => 'Lớp không tồn tại',
        'attendanceType.in'      => 'Loại điểm danh không hợp lệ',
        'filterStatus.in'        => 'Trạng thái lọc không hợp lệ',
        'attendanceNote.max'     => 'Ghi chú không được vượt quá 500 ký tự',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return array_merge([
            'selectedClassId' => ['as' => 'classId',  'except' => null],
            'attendanceType'  => ['as' => 'type',     'except' => 1],
            'selectedDate'    => ['as' => 'date',     'except' => null],
            'selectedKy'      => ['as' => 'ky',       'except' => null],
            'filterStatus'    => ['as' => 'status',   'except' => 'all'],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh'          => 'handleRefresh',
        'filterChanged'    => 'handleFilterChanged',
        'viewModeDetected' => 'setViewMode',
    ];

    // ==================== LIFECYCLE ====================

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount()
    {
        $this->students = collect();
        parent::mount();
        $this->requireParishId();

        if ($this->selectedClassId) {
            $class = CatechismClass::select('id', 'name', 'school_year_id', 'grade_level_id')
                ->find($this->selectedClassId);

            if ($class) {
                $this->selectedNamHoc    = $class->school_year_id;
                // $this->selectedKhoi      = $class->grade_level_id;
                $this->selectedClassName = $class->name;

                $this->loadStudents();
                $this->loadSessions();
                $this->loadAttendanceRecords();

                if ($this->viewMode === 'mobile') {
                    $this->loadSessionIndicators();
                }
            } else {
                $this->selectedClassId = null;
                session()->flash('warning', 'Lớp học không tồn tại');
            }
        }
    }

    protected function loadInitialData(): void
    {
        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }

        if (!$this->selectedClassId && $this->selectedNamHoc) {
            $this->selectedClassId = $this->getDefaultClassId();

            if ($this->selectedClassId) {
                $class = CatechismClass::select('id', 'name', 'school_year_id', 'grade_level_id')
                    ->find($this->selectedClassId);

                if ($class) {
                    // $this->selectedKhoi      = $class->grade_level_id;
                    $this->selectedClassName = $class->name;
                }
            }
        }

        if ($this->selectedClassId) {
            $this->loadStudents();
            $this->loadSessions();
            $this->loadAttendanceRecords();

            if ($this->viewMode === 'mobile') {
                $this->loadSessionIndicators();
            }
        }
    }

    // ==================== SANITIZE ====================

    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        $this->selectedClassId = is_numeric($this->selectedClassId)
            ? (int) $this->selectedClassId : null;

        $this->attendanceType = in_array((int) $this->attendanceType, [1, 2])
            ? (int) $this->attendanceType : 1;

        $this->selectedKy = is_numeric($this->selectedKy)
            && in_array((int) $this->selectedKy, [1, 2])
            ? (int) $this->selectedKy : null;

        if (!in_array($this->filterStatus, ['all', 'present', 'absent'])) {
            $this->filterStatus = 'all';
        }
    }

    protected function resetToDefaults(): void
    {
        parent::resetToDefaults();
        $this->filterStatus   = 'all';
        $this->attendanceType = 1;
        $this->selectedDate   = null;
    }

    // ==================== PROPERTY UPDATERS ====================

    public function updatedSearch(): void
    {
        parent::updatedSearch();
        $this->loadStudents();
    }

    public function updatedSelectedClassId(): void
    {
        $this->selectedClassId = is_numeric($this->selectedClassId)
            ? (int) $this->selectedClassId : null;

        if ($this->selectedClassId) {
            // Load class name ngay khi chọn lớp
            $this->selectedClassName = CatechismClass::where('id', $this->selectedClassId)
                ->value('name') ?? 'Chọn lớp';

            $this->loadStudents();
            $this->loadSessions();
            $this->loadAttendanceRecords();

            if ($this->viewMode === 'mobile') {
                $this->loadSessionIndicators();
            }
        } else {
            $this->selectedClassName = '';
            $this->clearAttendanceState();
        }

        $this->resetPage();
    }

    public function updatedSelectedDate(): void
    {
        if ($this->viewMode === 'mobile' && $this->selectedDate) {
            $this->loadAttendanceRecords();
        }
    }

    public function updatedAttendanceType(): void
    {
        $this->attendanceType = in_array((int) $this->attendanceType, [1, 2])
            ? (int) $this->attendanceType : 1;

        $this->loadSessions();
        $this->loadAttendanceRecords();

        if ($this->viewMode === 'mobile') {
            $this->loadSessionIndicators();
        }

        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        if (!in_array($this->filterStatus, ['all', 'present', 'absent'])) {
            $this->filterStatus = 'all';
        }
        $this->loadStudents();
    }

    // ==================== STATE MANAGEMENT ====================

    protected function clearAttendanceState(): void
    {
        $this->selectedClassId   = null;
        $this->selectedClassName = '';
        $this->students          = collect();
        $this->sessions          = [];
        $this->attendanceRecords = [];
        $this->sessionHasRecord  = [];
        $this->selectedDate      = null;

        // Thông báo Alpine reset draft
        $this->dispatchBrowserEvent('attendance-state-cleared');
    }

    // ==================== DATA LOADING ====================

    protected function loadStudents(): void
    {
        if (!$this->selectedClassId) {
            $this->students = collect();
            return;
        }

        try {
            $class = CatechismClass::with([
                'students' => function ($q) {
                    $q->wherePivot('status', 1)
                        ->orderBy('last_name')
                        ->orderBy('first_name');

                    if (!empty(trim($this->search))) {
                        $search = '%' . trim($this->search) . '%';
                        $q->where(
                            fn($qq) =>
                            $qq->where('first_name', 'like', $search)
                                ->orWhere('last_name', 'like', $search)
                        );
                    }
                },
                'students.saint',
            ])->find($this->selectedClassId);

            $this->students = $class
                ? $class->students->makeHidden(['qr_token', 'parishioner_id'])
                : collect();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading students');
            $this->students = collect();
            session()->flash('error', 'Không thể tải danh sách học sinh');
        }
    }

    protected function loadSessions(): void
    {
        if (!$this->selectedClassId) {
            $this->sessions     = [];
            $this->selectedDate = null;
            return;
        }

        if ($this->viewMode === 'mobile') {
            $this->sessions = AttendanceSession::where('class_id', $this->selectedClassId)
                ->where('type', $this->attendanceType)
                ->orderBy('date')
                ->get(['id', 'date', 'status'])  // chỉ 3 field
                ->map(fn($s) => [
                    'id'      => $s->id,
                    'dateStr' => Carbon::parse($s->date)->format('Y-m-d'),
                    'fullDate' => Carbon::parse($s->date)->format('d/m'),
                    'dayName' => $this->getVietnameseDayName(Carbon::parse($s->date)),
                    'locked'  => $s->status === AttendanceSession::STATUS_CLOSED,
                ])->toArray();

            $this->autoSelectDateForMobile();
            $this->loadAttendanceRecords();
            return;
        }

        try {
            $query = AttendanceSession::where('class_id', $this->selectedClassId)
                ->where('type', $this->attendanceType)
                ->when($this->selectedKy, fn($q) => $q->where('semester', $this->selectedKy))
                ->orderBy('date');

            // Mobile chỉ cần metadata nhẹ
            $sessions = $this->viewMode === 'mobile'
                ? $query->get(['id', 'date', 'type', 'status'])
                : $query->get();

            $this->sessions = $sessions->map(function ($s) {
                $date = Carbon::parse($s->date);
                return [
                    'id'       => $s->id,
                    'date'     => $date,
                    'dateStr'  => $date->format('Y-m-d'),
                    'fullDate' => $date->format('d/m'),
                    'dayName'  => $this->getVietnameseDayName($date),
                    'type'     => $s->type,
                    'status'   => $s->status,
                    'locked'   => $s->status === AttendanceSession::STATUS_CLOSED,
                ];
            })->toArray();

            // Mobile auto-pick ngày
            if ($this->viewMode === 'mobile' && !$this->selectedDate && !empty($this->sessions)) {
                $this->autoSelectDateForMobile();
                $this->loadAttendanceRecords();
                return;
            }

            if (empty($this->sessions)) {
                session()->flash('info', 'Chưa có buổi điểm danh nào');
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading sessions');
            $this->sessions          = [];
            $this->selectedDate      = null;
            $this->attendanceRecords = [];
        }
    }

    protected function loadAttendanceRecordsQuiet(): void
    {
        if (!$this->selectedClassId) {
            $this->attendanceRecords = [];
            return;
        }

        try {
            $query = AttendanceRecord::whereHas('session', function ($q) {
                $q->where('class_id', $this->selectedClassId)
                    ->where('type', $this->attendanceType);

                if ($this->viewMode === 'mobile' && $this->selectedDate) {
                    $q->whereDate('date', $this->selectedDate);
                }
            });

            $this->attendanceRecords = $query->get()
                ->groupBy(fn($r) => $r->student_id . '_' . $r->session_id)
                ->map(fn($group) => [
                    'status' => $group->first()->status,
                    'note'   => $group->first()->note,
                ])
                ->toArray();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading attendance records');
            $this->attendanceRecords = [];
        }
    }

    protected function loadAttendanceRecords(): void
    {
        if (!$this->selectedClassId) {
            $this->attendanceRecords = [];
            return;
        }

        $this->loadAttendanceRecordsQuiet();

        $this->dispatchBrowserEvent('attendance-records-loaded', [
            'records' => $this->attendanceRecords,
        ]);
    }

    protected function loadSessionIndicators(): void
    {
        if (!$this->selectedClassId || $this->viewMode !== 'mobile') {
            $this->sessionHasRecord = [];
            return;
        }

        $this->sessionHasRecord = AttendanceRecord::whereHas(
            'session',
            fn($q) =>
            $q->where('class_id', $this->selectedClassId)
                ->where('type', $this->attendanceType)
        )
            ->join('attendance_sessions', 'attendance_records.session_id', '=', 'attendance_sessions.id')
            ->selectRaw('DATE_FORMAT(attendance_sessions.date, "%Y-%m-%d") as date_str, COUNT(*) as total')
            ->groupBy('date_str')
            ->pluck('total', 'date_str')
            ->toArray();
    }

    /**
     * ✅ Method duy nhất Alpine gọi để lưu — thay thế setAttendance() và markAllPresent()
     * $draft = [ "studentId_sessionId" => ['status' => int, 'note' => string], ... ]
     */
    public function saveFromClient(array $draft): void
    {
        if (empty($draft)) {
            session()->flash('warning', 'Không có dữ liệu để lưu');
            return;
        }

        // ✅ Validate draft array để tránh edge cases
        foreach ($draft as $key => $item) {
            // Check key format: must be "studentId_sessionId"
            if (!preg_match('/^\d+_\d+$/', $key)) {
                session()->flash('error', 'Dữ liệu điểm danh không hợp lệ (key format)');
                return;
            }

            // Check status: must be int 1-3
            $status = $item['status'] ?? null;
            if (!is_int($status) || !in_array($status, [1, 2, 3])) {
                session()->flash('error', 'Trạng thái điểm danh không hợp lệ');
                return;
            }

            // Check note: must be string, max 500 chars
            $note = $item['note'] ?? '';
            if (!is_string($note) || strlen($note) > 500) {
                session()->flash('error', 'Ghi chú không hợp lệ hoặc quá dài');
                return;
            }
        }

        // Build drafts array theo format AttendanceService cần
        $drafts = collect($draft)
            ->map(function ($item, $key) {
                [$studentId, $sessionId] = explode('_', $key);
                return [
                    'student_id'     => (int) $studentId,
                    'session_id'     => (int) $sessionId,
                    'status'         => (int) ($item['status'] ?? 1),
                    'note'           => $item['note'] ?? '',
                    'attendanceType' => $this->attendanceType,
                ];
            })
            ->values()
            ->toArray();

        try {
            // ✅ Dispatch loading state cho Alpine
            $this->dispatchBrowserEvent('saving-attendance');

            $result = $this->attendanceService->saveBulkAttendance($drafts);

            if ($result['success']) {
                // 1. Load lại records từ DB
                $this->loadAttendanceRecordsQuiet();

                if ($this->viewMode === 'mobile') {
                    $this->loadSessionIndicators();
                }

                session()->flash('message', $result['message'] ?? 'Đã lưu điểm danh thành công');

                // ✅ Thông báo Alpine xóa draft sau khi lưu thành công
                $this->dispatchBrowserEvent('attendance-saved', [
                    'records' => $this->attendanceRecords,
                ]);
            } else {
                session()->flash('error', $result['message']);
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error saving attendance');
            session()->flash('error', 'Có lỗi khi lưu điểm danh');
        } finally {
            // ✅ Dispatch end loading state
            $this->dispatchBrowserEvent('attendance-save-completed');
        }
    }

    public function switchType(int $type): void
    {
        if ($type === $this->attendanceType) return;
        $this->attendanceType = $type;
        $this->selectedDate = null;
        $this->loadSessions();
        $this->loadAttendanceRecords();
        if ($this->viewMode === 'mobile') {
            $this->loadSessionIndicators();
        }
    }

    // ==================== NOTE MODAL ====================

    /**
     * Alpine gọi khi click P — server tìm student name và mở modal
     */
    public function openNoteModal($studentId, $sessionId): void
    {
        $session = collect($this->sessions)->firstWhere('id', (int) $sessionId);

        if (!$session || $session['locked']) {
            session()->flash('warning', 'Không thể điểm danh cho buổi này');
            return;
        }

        $student = \App\Models\StudentNew::find((int) $studentId);

        if (!$student) {
            session()->flash('error', 'Không tìm thấy học sinh');
            return;
        }

        $this->currentStudentId    = (int) $studentId;
        $this->currentSessionId    = (int) $sessionId;
        $this->currentStudentName  = $student->full_name_with_saint
            ?? $student->full_name
            ?? 'Học sinh';

        // Lấy note hiện tại từ draft (Alpine sẽ pass vào) hoặc từ DB
        $dbKey = $studentId . '_' . $sessionId;
        $this->attendanceNote = $this->attendanceRecords[$dbKey]['note'] ?? '';

        $this->showNoteModal = true;
    }

    /**
     * Lưu note từ modal — emit lại cho Alpine để Alpine cập nhật draft
     */
    public function saveAttendanceWithNote(): void
    {
        $this->validate(['attendanceNote' => 'nullable|string|max:500']);

        if (!$this->currentStudentId || !$this->currentSessionId) {
            session()->flash('error', 'Thiếu thông tin học sinh hoặc buổi học');
            return;
        }

        // ✅ Emit cho Alpine thay vì lưu vào $draftAttendance
        $this->dispatchBrowserEvent('note-saved', [
            'key'    => $this->currentStudentId . '_' . $this->currentSessionId,
            'status' => AttendanceRecord::STATUS_ABSENT_EXCUSED,
            'note'   => trim($this->attendanceNote),
        ]);

        session()->flash('message', 'Đã ghi nhận vắng có phép');
        $this->closeNoteModal();
    }

    public function closeNoteModal(): void
    {
        $this->showNoteModal       = false;
        $this->currentStudentId    = null;
        $this->currentSessionId    = null;
        $this->currentStudentName  = '';
        $this->attendanceNote      = '';
        $this->resetValidation(['attendanceNote']);
    }

    // ==================== EVENT HANDLERS ====================

    public function handleFilterChanged($filters): void
    {
        if (!is_array($filters)) return;

        $namHocChanged = false;
        $khoiChanged   = false;

        if (array_key_exists('namHoc', $filters)) {
            $newNamHoc = is_numeric($filters['namHoc']) ? (int) $filters['namHoc'] : null;
            if ($newNamHoc !== $this->selectedNamHoc) {
                $this->selectedNamHoc = $newNamHoc;
                $namHocChanged = true;
            }
        }

        if (array_key_exists('khoi', $filters)) {
            $newKhoi = is_numeric($filters['khoi']) ? (int) $filters['khoi'] : null;
            if ($newKhoi !== $this->selectedKhoi) {
                $this->selectedKhoi = $newKhoi;
                $khoiChanged = true;
            }
        }

        if ($namHocChanged || $khoiChanged) {
            $this->clearAttendanceState();
        }

        if (array_key_exists('lop', $filters)) {
            $newClassId = is_numeric($filters['lop']) ? (int) $filters['lop'] : null;

            if ($newClassId !== $this->selectedClassId) {
                $this->selectedClassId = $newClassId;
                $this->selectedDate = null;

                if ($this->selectedClassId) {
                    $this->selectedClassName = CatechismClass::where('id', $this->selectedClassId)
                        ->value('name') ?? '';

                    $this->loadStudents();
                    $this->loadSessions();
                    $this->loadAttendanceRecords();

                    if ($this->viewMode === 'mobile') {
                        $this->loadSessionIndicators();
                    }
                } else {
                    $this->clearAttendanceState();
                }
            }
        }

        if (array_key_exists('ky', $filters)) {
            $newKy = is_numeric($filters['ky']) ? (int) $filters['ky'] : null;
            if ($newKy !== $this->selectedKy) {
                $this->selectedKy = $newKy;
                if ($this->selectedClassId) {
                    $this->loadSessions();
                    $this->loadAttendanceRecords();
                    if ($this->viewMode === 'mobile') {
                        $this->loadSessionIndicators();
                    }
                }
            }
        }

        $this->resetPage();
    }

    public function setViewMode(string $mode): void
    {
        if ($this->viewMode !== $mode) {
            $this->viewMode = $mode;

            if ($this->selectedClassId) {
                $this->loadSessions();
                $this->loadAttendanceRecords();

                if ($mode === 'mobile') {
                    $this->loadSessionIndicators();
                }
            }
        }
    }

    // ==================== HELPERS ====================

    protected function autoSelectDateForMobile(): void
    {
        if (empty($this->sessions)) return;

        $today = Carbon::today()->format('Y-m-d');
        $sessions = collect($this->sessions)->sortBy('dateStr');

        $todaySession = $sessions->firstWhere('dateStr', $today);
        if ($todaySession) {
            $this->selectedDate = $today;
            return;
        }

        // Ngày trước gần nhất
        $prev = $sessions->last(fn($s) => $s['dateStr'] < $today);
        if ($prev) {
            $this->selectedDate = $prev['dateStr'];
            return;
        }

        // Ngày sau gần nhất
        $next = $sessions->first(fn($s) => $s['dateStr'] > $today);
        if ($next) {
            $this->selectedDate = $next['dateStr'];
            return;
        }

        $this->selectedDate = $sessions->first()['dateStr'];
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;

        if ($this->viewMode === 'mobile') {
            $this->loadAttendanceRecords();
        }
    }

    protected function getVietnameseDayName(Carbon $date): string
    {
        return ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'][$date->dayOfWeek];
    }

    protected function getDefaultNamHocId(): ?int
    {
        $today = now()->toDateString();

        $current = NamHoc::where('parish_id', $this->parishId)
            ->where('status', true)
            ->where(
                fn($q) => $q
                    ->where(fn($q1) => $q1
                        ->whereDate('start_date_one', '<=', $today)
                        ->whereDate('end_date_one', '>=', $today))
                    ->orWhere(fn($q2) => $q2
                        ->whereDate('start_date_two', '<=', $today)
                        ->whereDate('end_date_two', '>=', $today))
            )->value('id');

        if ($current) return $current;

        $upcoming = NamHoc::where('parish_id', $this->parishId)
            ->where('status', true)
            ->whereDate('end_date_two', '>=', $today)
            ->orderBy('start_date_one')
            ->value('id');

        if ($upcoming) return $upcoming;

        return NamHoc::where('parish_id', $this->parishId)
            ->where('status', true)
            ->orderByDesc('id')
            ->value('id');
    }

    protected function getDefaultClassId(): ?int
    {
        if (!$this->selectedNamHoc) {
            return null;
        }

        return CatechismClass::where('school_year_id', $this->selectedNamHoc)
            ->where('is_active', true)
            ->orderBy('name')
            ->value('id');
    }

    // ==================== COMPUTED ====================

    public function getSelectedClassNameProperty(): string
    {
        return $this->selectedClassName ?: 'Chọn lớp';
    }

    // ==================== RENDER ====================

    public function render()
    {
        if ($this->selectedClassId && empty($this->students)) {
            $this->loadStudents();
        }

        $students = $this->students ?? collect();

        // ✅ Optimize: Pre-build grid từ attendanceRecords (O(1) lookup thay vì N×M)
        $grid = [];
        foreach ($students as $student) {
            $grid[$student->id] = [];
            foreach ($this->sessions as $session) {
                $key = $student->id . '_' . $session['id'];
                $grid[$student->id][$session['id']] = $this->attendanceRecords[$key]['status'] ?? null;
            }
        }

        // ✅ Optimize: Pre-compute stats với array_map để giảm loops
        $stats = array_map(function ($session) use ($students) {
            $dateStr = $session['dateStr'];
            $s = ['present' => 0, 'absentPermitted' => 0, 'absentNotPermitted' => 0];

            foreach ($students as $student) {
                $key = $student->id . '_' . $session['id'];
                $status = $this->attendanceRecords[$key]['status'] ?? null;
                match ($status) {
                    AttendanceRecord::STATUS_PRESENT          => $s['present']++,
                    AttendanceRecord::STATUS_ABSENT_EXCUSED   => $s['absentPermitted']++,
                    AttendanceRecord::STATUS_ABSENT_UNEXCUSED => $s['absentNotPermitted']++,
                    default => null,
                };
            }

            return $s;
        }, $this->sessions);

        // Convert to associative array for view
        $statsAssoc = [];
        foreach ($this->sessions as $index => $session) {
            $statsAssoc[$session['dateStr']] = $stats[$index];
        }

        $layout = auth()->user()?->isCatechist()
            ? 'frontend.layout.catechist'
            : 'frontend.layout.main';

        return view('livewire.attendance-manager', [
            'students' => $students,
            'parishId'       => $this->parishId,
            'attendanceGrid' => $grid,
            'sessionStats'   => $statsAssoc,
        ])->extends($layout)->section('content');
    }
}
