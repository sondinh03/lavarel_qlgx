<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use App\Models\NamHoc;
use App\Services\AttendanceService;
use Carbon\Carbon;

class AttendanceManager extends BaseComponent
{
    /** @var AttendanceService */
    protected AttendanceService $attendanceService;
    
    // ==================== FILTERS ====================

    /** @var int|null Selected năm học ID */
    public $selectedNamHoc = null;

    /** @var int|string Selected khối ('' = all) */
    public $selectedKhoi = '';

    /** @var int|null Selected lớp */
    public $selectedClassId = null;

    /** @var int|string Selected kỳ */
    public $selectedKy = '';

    /** @var int Attendance type (1: học, 2: lễ) */
    public $attendanceType = 1;

    /** @var string Filter by attendance status */
    public $filterStatus = 'all';

    // ==================== VIEW STATE ====================

    /** @var string View mode (desktop/mobile) */
    public $viewMode = 'desktop';

    /** @var string|null Selected date for mobile view */
    public $selectedDate = null;

    // ==================== NOTE MODAL ====================

    /** @var bool Show note modal */
    public $showNoteModal = false;
    public $currentStudentId = null;
    public $currentSessionId = null;
    public $currentStudentName = '';
    public $attendanceNote = '';

    // ==================== DATA ====================

    /** @var \Illuminate\Support\Collection */
    public $students;

    /** @var array */
    public $sessions = [];

    /** @var array Indexed by student_id_date */
    public $attendanceRecords = [];

    /** @var array Draft attendance (chưa lưu DB) */
    public $draftAttendance = [];

    // ==================== VALIDATION ====================

    protected $rules = [
        'selectedClassId'  => 'nullable|integer|exists:classes,id',
        'attendanceType'   => 'required|integer|in:1,2',
        'filterStatus'     => 'required|string|in:all,present,absent',
        'search'           => 'nullable|string|max:255',
        'attendanceNote'   => 'nullable|string|max:500',
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
            'selectedClassId' => ['as' => 'classId', 'except' => null],
            'attendanceType'  => ['as' => 'type', 'except' => 1],
            'selectedDate'    => ['as' => 'date', 'except' => null],
            'filterStatus'    => ['as' => 'status', 'except' => 'all'],
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
        // Khởi tạo collection trước khi parent::mount() gọi loadInitialData()
        $this->students = collect();

        parent::mount();

        $this->requireParishId();

        // Nếu có classId trên URL, resolve ngược lên namHoc + khoi
        if ($this->selectedClassId) {
            $class = CatechismClass::select('school_year_id', 'grade_level_id')
                ->find($this->selectedClassId);

            if ($class) {
                $this->selectedNamHoc = $class->school_year_id;
                $this->selectedKhoi   = $class->grade_level_id;
            } else {
                $this->selectedClassId = null;
                session()->flash('warning', 'Lớp học không tồn tại hoặc không có quyền truy cập');
            }
        }
    }

    protected function loadInitialData(): void
    {
        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }

        if ($this->selectedClassId) {
            $this->loadStudents();
            $this->loadSessions();
            $this->loadAttendanceRecords();
        }
    }

    // ==================== SANITIZE ====================

    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        $this->selectedClassId = is_numeric($this->selectedClassId)
            ? (int) $this->selectedClassId
            : null;

        $this->attendanceType = in_array((int) $this->attendanceType, [1, 2])
            ? (int) $this->attendanceType
            : 1;

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
            ? (int) $this->selectedClassId
            : null;

        if ($this->selectedClassId) {
            $this->loadStudents();
            $this->loadSessions();
            $this->loadAttendanceRecords();
        } else {
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
        if (!empty($this->draftAttendance)) {
            session()->flash('warning', 'Bạn có dữ liệu chưa lưu');
            return;
        }

        $this->attendanceType = in_array((int) $this->attendanceType, [1, 2])
            ? (int) $this->attendanceType
            : 1;

        $this->loadSessions();
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

    /**
     * Reset toàn bộ state điểm danh — dùng khi đổi năm học / khối
     */
    protected function clearAttendanceState(): void
    {
        $this->selectedClassId  = null;
        $this->students         = collect();
        $this->sessions         = [];
        $this->attendanceRecords = [];
        $this->draftAttendance  = [];
        $this->selectedDate     = null;
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
                        $q->where(function ($qq) use ($search) {
                            $qq->where('first_name', 'like', $search)
                                ->orWhere('last_name', 'like', $search);
                        });
                    }
                },
                'students.saint',
            ])->find($this->selectedClassId);

            $this->students = $class ? $class->students : collect();

            // Ẩn các field không cần thiết trên frontend
            $this->students->makeHidden(['qr_token', 'parishioner_id']);
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

        try {
            $query = AttendanceSession::where('class_id', $this->selectedClassId)
                ->where('type', $this->attendanceType)
                ->orderBy('date');

            // Mobile: chỉ load session của ngày đang chọn (sau khi đã chọn ngày)
            // Lần đầu (selectedDate == null) load tất cả để pick ngày
            if ($this->viewMode === 'mobile' && $this->selectedDate) {
                $query->whereDate('date', $this->selectedDate);
            }

            $sessions = $query->get();

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

            // Mobile: auto-pick ngày nếu chưa có
            if ($this->viewMode === 'mobile' && !$this->selectedDate && !empty($this->sessions)) {
                $this->autoSelectDateForMobile();
                // Sau khi chọn ngày, load lại chỉ session của ngày đó
                $this->loadSessions();
            }

            if (empty($this->sessions)) {
                session()->flash('info', 'Chưa có buổi điểm danh nào');
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading sessions', ['class_id' => $this->selectedClassId]);
            $this->sessions     = [];
            $this->selectedDate = null;
            $this->attendanceRecords = [];
            session()->flash('error', 'Không thể tải danh sách buổi học');
        }
    }

    protected function loadAttendanceRecords(): void
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
            })->with('session');

            $this->attendanceRecords = $query->get()
                ->groupBy(function ($r) {
                    return $r->student_id . '_' . $r->session_id;
                })
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

    // ==================== EVENT HANDLERS ====================

    public function handleFilterChanged($filters): void
    {
        if (!is_array($filters)) {
            return;
        }

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

                if ($this->selectedClassId) {
                    $this->loadStudents();
                    $this->loadSessions();
                    $this->loadAttendanceRecords();
                } else {
                    $this->clearAttendanceState();
                }
            }
        }

        if (array_key_exists('ky', $filters)) {
            $this->selectedKy = is_numeric($filters['ky']) ? (int) $filters['ky'] : null;
        }

        $this->resetPage();
    }

    // ==================== NOTE MODAL ====================

    public function openNoteModal($studentId, $sessionId): void
    {
        $session = collect($this->sessions)->firstWhere('id', $sessionId);

        if (!$session || $session['locked']) {
            session()->flash('warning', 'Không thể điểm danh cho buổi này');
            return;
        }

        $student = $this->students->firstWhere('id', $studentId);

        if (!$student) {
            session()->flash('error', 'Không tìm thấy học sinh');
            return;
        }

        $this->currentStudentId   = $studentId;
        $this->currentSessionId   = $sessionId;
        $this->currentStudentName = $student->full_name_with_saint;

        $key = $studentId . '_' . $sessionId;
        $this->attendanceNote = $this->draftAttendance[$key]['note'] ?? '';

        $this->showNoteModal = true;
    }

    public function saveAttendanceWithNote(): void
    {
        $this->validate(['attendanceNote' => 'nullable|string|max:500']);

        if (!$this->currentStudentId || !$this->currentSessionId) {
            session()->flash('error', 'Thiếu thông tin học sinh hoặc buổi học');
            return;
        }

        $key = $this->currentStudentId . '_' . $this->currentSessionId;

        $this->draftAttendance[$key] = [
            'student_id'     => $this->currentStudentId,
            'session_id'     => $this->currentSessionId,
            'status'         => AttendanceRecord::STATUS_ABSENT_EXCUSED,
            'note'           => trim($this->attendanceNote),
            'attendanceType' => $this->attendanceType,
        ];

        session()->flash('success', 'Đã ghi nhận vắng có phép (chưa lưu vào CSDL)');
        $this->closeNoteModal();
    }

    public function closeNoteModal(): void
    {
        $this->showNoteModal      = false;
        $this->currentStudentId   = null;
        $this->currentSessionId   = null;
        $this->currentStudentName = '';
        $this->attendanceNote     = '';
        $this->resetValidation(['attendanceNote']);
    }

    // ==================== ATTENDANCE ACTIONS ====================

    public function setAttendance($studentId, $sessionId, $status): void
    {
        $session = collect($this->sessions)->firstWhere('id', $sessionId);

        if (!$session || $session['locked']) {
            session()->flash('warning', 'Không thể điểm danh cho buổi này');
            return;
        }

        $key = $studentId . '_' . $sessionId;

        if ($status === null || $status === 'null') {
            unset($this->draftAttendance[$key]);
        } else {
            $this->draftAttendance[$key] = [
                'student_id'     => $studentId,
                'session_id'     => $sessionId,
                'status'         => (int) $status,
                'note'           => '',
                'attendanceType' => $this->attendanceType,
            ];
        }
    }

    public function markAllPresent($sessionId): void
    {
        $session = collect($this->sessions)->firstWhere('id', $sessionId);

        if (!$session || $session['locked']) {
            session()->flash('error', 'Không thể điểm danh cho buổi này');
            return;
        }

        foreach ($this->students as $student) {
            $key = $student->id . '_' . $sessionId;
            $this->draftAttendance[$key] = [
                'student_id'     => $student->id,
                'session_id'     => $sessionId,
                'status'         => AttendanceRecord::STATUS_PRESENT,
                'note'           => '',
                'attendanceType' => $this->attendanceType,
            ];
        }

        session()->flash('success', 'Đã đánh dấu tất cả có mặt (lưu tạm)');
    }

    public function saveAttendance(): void
    {
        $drafts = collect($this->draftAttendance)
            ->where('attendanceType', $this->attendanceType)
            ->values()
            ->toArray();

        if (empty($drafts)) {
            session()->flash('warning', 'Không có dữ liệu để lưu');
            return;
        }

        try {
            $result = $this->attendanceService->saveBulkAttendance($drafts);

            if ($result['success']) {
                // Chỉ xóa drafts của type hiện tại
                $this->draftAttendance = collect($this->draftAttendance)
                    ->where('attendanceType', '!=', $this->attendanceType)
                    ->toArray();

                $this->loadAttendanceRecords();
                session()->flash('message', $result['message'] ?? 'Đã lưu điểm danh thành công');
                $this->dispatchBrowserEvent('attendanceSaved');
            } else {
                session()->flash('error', $result['message']);
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error saving attendance', [
                'drafts_count' => count($drafts),
            ]);
            session()->flash('error', 'Có lỗi khi lưu điểm danh');
        }
    }

    public function discardDrafts(): void
    {
        $count = count($this->draftAttendance);
        $this->draftAttendance = [];
        session()->flash('message', "Đã hủy {$count} thay đổi chưa lưu");
        $this->dispatchBrowserEvent('draftsDiscarded');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Auto-pick ngày cho mobile (chỉ đọc $this->sessions, không gọi thêm gì)
     */
    protected function autoSelectDateForMobile(): void
    {
        if (empty($this->sessions)) {
            return;
        }

        $today = Carbon::today()->format('Y-m-d');

        $todaySession = collect($this->sessions)->firstWhere('dateStr', $today);

        if ($todaySession) {
            $this->selectedDate = $todaySession['dateStr'];
            return;
        }

        $unlocked = collect($this->sessions)->first(fn($s) => !$s['locked']);
        $this->selectedDate = $unlocked
            ? $unlocked['dateStr']
            : $this->sessions[0]['dateStr'];
    }

    protected function getVietnameseDayName(Carbon $date): string
    {
        return ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'][$date->dayOfWeek];
    }

    protected function getDefaultNamHocId(): ?int
    {
        return NamHoc::where('parish_id', $this->parishId)
            ->where('status', true)
            ->orderByDesc('id')
            ->value('id');
    }

    // ==================== PUBLIC HELPERS (dùng trong blade) ====================

    public function getAttendanceStatus($studentId, $dateStr)
    {
        $session = collect($this->sessions)->firstWhere('dateStr', $dateStr);

        if (!$session) {
            return null;
        }

        $key = $studentId . '_' . $session['id'];

        // Draft trước, DB sau
        if (isset($this->draftAttendance[$key])) {
            return $this->draftAttendance[$key]['status'];
        }

        return $this->attendanceRecords[$key]['status'] ?? null;
    }

    public function getDateStats($dateStr): array
    {
        $stats = ['present' => 0, 'absentPermitted' => 0, 'absentNotPermitted' => 0];

        foreach ($this->students as $student) {
            $status = $this->getAttendanceStatus($student->id, $dateStr);
            match ($status) {
                AttendanceRecord::STATUS_PRESENT          => $stats['present']++,
                AttendanceRecord::STATUS_ABSENT_EXCUSED   => $stats['absentPermitted']++,
                AttendanceRecord::STATUS_ABSENT_UNEXCUSED => $stats['absentNotPermitted']++,
                default                                   => null,
            };
        }

        return $stats;
    }

    public function setViewMode(string $mode): void
    {
        if ($this->viewMode !== $mode) {
            $this->viewMode = $mode;

            if ($this->selectedClassId) {
                $this->selectedDate = null;
                $this->loadSessions();
            }
        }
    }

    // ==================== COMPUTED PROPERTIES ====================

    public function getSelectedClassNameProperty(): string
    {
        if (!$this->selectedClassId) {
            return 'Chọn lớp';
        }

        return CatechismClass::where('id', $this->selectedClassId)->value('name') ?? 'Chọn lớp';
    }

    public function getPendingCountProperty(): int
    {
        return count($this->draftAttendance);
    }

    public function getHasUnsavedChangesProperty(): bool
    {
        return !empty($this->draftAttendance);
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.attendance-manager', [
            'parishId' => $this->parishId,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
