<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Lop;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Hamcrest\Type\IsNumeric;

use function Aws\filter;

class AttendanceManager extends BaseComponent
{
    // ==================== FILTERS ====================
    /** @var int|null Selected lớp ID */
    public $selectedClassId = null;

    /** @var int|null Selected năm học ID */
    public $selectedNamHoc = null;

    /** @var int|string Selected khối ('' = all) */
    public $selectedKhoi = '';

    /** @var int|string Selected lớp ('' = all) */
    public $selectedLop = '';

    /** @var int|string Selected kỳ ('' = all) */
    public $selectedKy = '';

    /** @var int Attendance type (1: học, 2: lễ) */
    public $attendanceType = 1;

    /** @var string Filter by attendance status */
    public $filterStatus = 'all'; // all, present, absent

    // ==================== VIEW STATE ====================

    /** @var string View mode (desktop/mobile) */
    public $viewMode = 'desktop';

    /** @var string|null Selected date for mobile view */
    public $selectedDate = null;
    
    // ==================== DATA ====================

    /** @var \Illuminate\Support\Collection Students of selected class */
    public $students;

    /** @var array Attendance sessions */
    public $sessions = [];

    /** @var array Attendance records (indexed by student_id_date) */
    public $attendanceRecords = [];

    /** @var array Draft attendance (chưa lưu DB) */
    public $draftAttendance = [];

    /** @var AttendanceService */
    protected $attendanceService;

    // ==================== VALIDATION ====================

    protected $rules = [
        'selectedClassId' => 'nullable|integer|exists:lop,id',
        'attendanceType' => 'required|integer|in:1,2',
        'filterStatus' => 'required|string|in:all,present,absent',
        'search' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'selectedClassId.exists' => 'Lớp không tồn tại',
        'attendanceType.in' => 'Loại điểm danh không hợp lệ',
        'filterStatus.in' => 'Trạng thái lọc không hợp lệ',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return array_merge([
            'selectedClassId' => ['as' => 'classId', 'except' => null],
            'attendanceType' => ['as' => 'type', 'except' => 1],
            'selectedDate' => ['as' => 'date', 'except' => null],
            'filterStatus' => ['as' => 'status', 'except' => 'all'],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh' => 'handleRefresh',
        'filterChanged' => 'handleFilterChanged',
    ];

    // ==================== LIFECYCLE ====================

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount($classId = null)
    {
        $this->authorize('viewAny', Lop::class);
        parent::mount();

        // $this->students = collect();
        // $this->sessions = [];
        // $this->attendanceRecords = [];
        // $this->draftAttendance = [];
        // $this->selectedDate = null;

        // Require parish ID
        $this->requireParishId();

        // Initialize from route param
        if ($classId) {
            $this->selectedClassId = $classId;
            $this->loadClassInfo();
        }

        // Detect view mode
        $this->detectViewMode();
    }

    protected function loadInitialData(): void
    {
        if ($this->selectedClassId) {
            $this->loadStudents();
            $this->loadSessions();
        }
    }

    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        // Sanitize selectedClassId
        $this->selectedClassId = is_numeric($this->selectedClassId)
            ? (int) $this->selectedClassId
            : null;

        // Sanitize attendanceType
        $this->attendanceType = in_array($this->attendanceType, [1, 2])
            ? (int) $this->attendanceType
            : 1;

        // Sanitize filterStatus
        if (!in_array($this->filterStatus, ['all', 'present', 'absent'])) {
            $this->filterStatus = 'all';
        }
    }

    protected function resetToDefaults(): void
    {
        parent::resetToDefaults();
        $this->filterStatus = 'all';
        $this->attendanceType = 1;
        $this->selectedDate = null;
    }


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
            $this->loadClassInfo();
            $this->loadStudents();
            $this->loadSessions();
        } else {
            $this->reset(['students', 'sessions', 'attendanceRecords', 'draftAttendance']);
        }

        $this->resetPage();
    }

    public function updatedAttendanceType(): void
    {
        if (!empty($this->draftAttendance)) {
            session()->flash('warning', 'Bạn có dữ liệu chưa lưu');
            return;
        }

        $this->attendanceType = in_array($this->attendanceType, [1, 2])
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

    /**
     * Reset toàn bộ state điểm danh
     * Dùng khi đổi Năm học / Khối
     */
    protected function clearAttendanceState(): void
    {
        $this->selectedClassId = null;

        $this->students = collect();
        $this->sessions = [];
        $this->attendanceRecords = [];
        $this->draftAttendance = [];
        $this->selectedDate = null;
    }

    // ==================== DATA LOADING ====================

    protected function loadClassInfo(): void
    {
        if (!$this->selectedClassId) {
            return;
        }

        try {
            $lop = Lop::with('blockRelation')
                ->ofParish($this->parishId)
                ->findOrFail($this->selectedClassId);

            $this->selectedNamHoc = $lop->schoolyear;
            $this->selectedKhoi = $lop->block;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading class info', [
                'class_id' => $this->selectedClassId,
            ]);
            session()->flash('error', 'Không tìm thấy lớp học');
        }
    }

    protected function loadStudents(): void
    {
        if (!$this->selectedClassId) {
            $this->students = collect();
            return;
        }

        try {
            $lop = Lop::with(['students' => function ($q) {
                // Active students only
                $q->wherePivot('status', 1);

                // Apply search
                if (!empty(trim($this->search))) {
                    $search = '%' . trim($this->search) . '%';
                    $q->where(function ($qq) use ($search) {
                        $qq->where('saint_name', 'like', $search)
                            ->orWhere('name', 'like', $search)
                            ->orWhere('last_name', 'like', $search);
                    });
                }

                // Order by name
                $q->orderBy('name');
            }])->find($this->selectedClassId);

            $this->students = $lop ? $lop->students : collect();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading students');
            $this->students = collect();
            session()->flash('error', 'Không thể tải danh sách học sinh');
        }
    }

    protected function loadSessions(): void
    {
        if (!$this->selectedClassId) {
            $this->reset(['sessions', 'selectedDate', 'attendanceRecords']);
            return;
        }

        try {
            $sessions = AttendanceSession::where('class_id', $this->selectedClassId)
                ->when($this->attendanceType, function ($q) {
                    return $q->where('type', $this->attendanceType);
                })
                ->orderBy('date')
                ->get();

            $this->sessions = $sessions->map(function ($s) {
                $date = Carbon::parse($s->date);

                return [
                    'id' => $s->id,
                    'date' => $date,
                    'dateStr' => $date->format('Y-m-d'),
                    'fullDate' => $date->format('d/m'),
                    'dayName' => $this->getVietnameseDayName($date),
                    'type' => $s->type,
                    'status' => $s->status,
                    'locked' => $s->status == AttendanceSession::STATUS_CLOSED,
                ];
            })->toArray();

            // Load attendance records
            $this->loadAttendanceRecords();

            // Auto-select first unlocked date for mobile
            if (!$this->selectedDate && !empty($this->sessions)) {
                $unlocked = collect($this->sessions)->first(fn($s) => !$s['locked']);
                $this->selectedDate = $unlocked
                    ? $unlocked['dateStr']
                    : $this->sessions[0]['dateStr'];
            }

            if (empty($this->sessions)) {
                session()->flash('info', 'Chưa có buổi điểm danh nào');
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading sessions', [
                'class_id' => $this->selectedClassId,
            ]);

            $this->reset(['sessions', 'selectedDate', 'attendanceRecords']);
            session()->flash('error', 'Không thể tải danh sách buổi học');
        }
    }

    protected function loadAttendanceRecords(): void
    {
        $sessionIds = collect($this->sessions)->pluck('id')->toArray();

        if (empty($sessionIds)) {
            $this->attendanceRecords = [];
            return;
        }

        try {
            $records = AttendanceRecord::whereIn('session_id', $sessionIds)
                ->with('session')
                ->get()
                ->groupBy(function ($r) {
                    return $r->student_id . '_' . Carbon::parse($r->session->date)->format('Y-m-d');
                });

            $this->attendanceRecords = $records->map(fn($g) => $g->first())->toArray();
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
            $newNamHoc = is_numeric($filters['namHoc'])
                ? (int) $filters['namHoc']
                : null;

            if ($newNamHoc !== $this->selectedNamHoc) {
                $this->selectedNamHoc = $newNamHoc;
                $namHocChanged = true;
            }
        }

        if (array_key_exists('khoi', $filters)) {
            $newKhoi = is_numeric($filters['khoi'])
                ? (int) $filters['khoi']
                : null;

            if ($newKhoi !== $this->selectedKhoi) {
                $this->selectedKhoi = $newKhoi;
                $khoiChanged = true;
            }
        }

        if ($namHocChanged || $khoiChanged) {
            $this->clearAttendanceState();
        }

        if (array_key_exists('lop', $filters)) {
            $newClassId = is_numeric($filters['lop'])
                ? (int) $filters['lop']
                : null;

            // ✅ Chỉ reload nếu lớp thay đổi
            if ($newClassId !== $this->selectedClassId) {
                $this->selectedClassId = $newClassId;

                if ($this->selectedClassId) {
                    $this->loadClassInfo();
                    $this->loadStudents();
                    $this->loadSessions();
                } else {
                    $this->reset(['students', 'sessions', 'attendanceRecords', 'draftAttendance']);
                }
            }
        }

        if (array_key_exists('ky', $filters)) {
            $this->selectedKy = is_numeric($filters['ky'])
                ? (int) $filters['ky']
                : null;
        }

        $this->resetPage();
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

        if ($status === null) {
            unset($this->draftAttendance[$key]);
        } else {
            $this->draftAttendance[$key] = [
                'student_id' => $studentId,
                'session_id' => $sessionId,
                'status' => $status,
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
                'student_id' => $student->id,
                'session_id' => $sessionId,
                'status' => AttendanceRecord::STATUS_PRESENT,
                'attendanceType' => $this->attendanceType,
            ];
        }

        session()->flash('success', 'Đã đánh dấu tất cả có mặt (lưu tạm)');
    }

    public function saveAttendance(): void
    {
        $this->requireManager();

        $drafts = collect($this->draftAttendance)
            ->where('attendanceType', $this->attendanceType);

        if ($drafts->isEmpty()) {
            session()->flash('warning', 'Không có dữ liệu để lưu');
            return;
        }

        try {
            $result = $this->attendanceService->saveBulkAttendance($drafts->values()->toArray());

            if ($result['success']) {
                // Clear saved drafts
                foreach ($drafts as $key => $item) {
                    unset($this->draftAttendance[$key]);
                }

                $this->loadAttendanceRecords();
                session()->flash('message', 'Đã lưu điểm danh thành công');
            } else {
                session()->flash('error', $result['message']);
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error saving attendance');
            session()->flash('error', 'Có lỗi khi lưu điểm danh');
        }
    }

    // ==================== HELPER METHODS ====================

    protected function getVietnameseDayName($date): string
    {
        $days = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
        return $days[$date->dayOfWeek];
    }

    public function getAttendanceStatus($studentId, $dateStr)
    {
        $session = collect($this->sessions)->firstWhere('dateStr', $dateStr);

        if (!$session) {
            return null;
        }

        // Check draft first
        $draftKey = $studentId . '_' . $session['id'];
        if (isset($this->draftAttendance[$draftKey])) {
            return $this->draftAttendance[$draftKey]['status'];
        }

        // Check saved records
        $recordKey = $studentId . '_' . $dateStr;
        return $this->attendanceRecords[$recordKey]['status'] ?? null;
    }

    public function getDateStats($dateStr): array
    {
        $stats = [
            'present' => 0,
            'absentPermitted' => 0,
            'absentNotPermitted' => 0,
        ];

        foreach ($this->students as $student) {
            $status = $this->getAttendanceStatus($student->id, $dateStr);

            match ($status) {
                AttendanceRecord::STATUS_PRESENT => $stats['present']++,
                AttendanceRecord::STATUS_ABSENT_EXCUSED => $stats['absentPermitted']++,
                AttendanceRecord::STATUS_ABSENT_UNEXCUSED => $stats['absentNotPermitted']++,
                default => null,
            };
        }

        return $stats;
    }

    protected function detectViewMode(): void
    {
        // Default to desktop, can be enhanced with JS detection
        $this->viewMode = 'desktop';
    }

    // ==================== COMPUTED PROPERTIES ====================

    public function getSelectedClassNameProperty(): string
    {
        if (!$this->selectedClassId) {
            return 'Chọn lớp';
        }

        return Lop::where('id', $this->selectedClassId)
            ->value('name') ?? 'Chọn lớp';
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

    // /**
    //  * Render component
    //  */
    // public function render()
    // {
    //     return view('livewire.attendance-manager')
    //         ->extends('frontend.layout.main')
    //         ->section('content');
    // }
