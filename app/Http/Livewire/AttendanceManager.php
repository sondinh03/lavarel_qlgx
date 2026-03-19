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

    public $viewMode     = 'desktop';
    public $selectedDate = null;

    // ==================== NOTE MODAL ====================

    public $showNoteModal      = false;
    public $currentStudentId   = null;
    public $currentSessionId   = null;
    public $currentStudentName = '';
    public $attendanceNote     = '';

    // ==================== DATA ====================

    protected $students;
    public $sessions          = [];
    public $attendanceRecords = [];
    public $sessionHasRecord  = [];

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
            'selectedClassId' => ['as' => 'classId', 'except' => null],
            'attendanceType'  => ['as' => 'type',    'except' => 1],
            'selectedDate'    => ['as' => 'date',    'except' => null],
            'selectedKy'      => ['as' => 'ky',      'except' => null],
            'filterStatus'    => ['as' => 'status',  'except' => 'all'],
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
                $this->selectedClassName = $class->name;

                $this->loadStudents();
                $this->loadSessions();
                $this->loadAttendanceRecords(); // FIX 1: gọi ngoài, không nhúng trong loadSessions()
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
                    $this->selectedClassName = $class->name;
                }
            }
        }

        if ($this->selectedClassId) {
            $this->loadStudents();
            $this->loadSessions();
            $this->loadAttendanceRecords(); // FIX 1: gọi ngoài
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
            $this->selectedClassName = CatechismClass::where('id', $this->selectedClassId)
                ->value('name') ?? 'Chọn lớp';

            $this->loadStudents();
            $this->loadSessions();
            $this->loadAttendanceRecords(); // FIX 1+2: 1 lần duy nhất, cả desktop lẫn mobile
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

        $this->selectedDate = null;
        $this->loadSessions();
        $this->loadAttendanceRecords(); // FIX 1+2: gọi 1 lần ngoài, không phân biệt desktop/mobile

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
                    $q->select(
                        'students.id',
                        'students.saint_id',   // bắt buộc cho eager load saint
                        'students.last_name',
                        'students.first_name',
                        'students.birthday',
                    )
                        ->wherePivot('status', 1)
                        ->orderBy('first_name')
                        ->orderBy('last_name');

                    if (!empty(trim($this->search))) {
                        $search = '%' . trim($this->search) . '%';
                        $q->where(
                            fn($qq) =>
                            $qq->where('first_name', 'like', $search)
                                ->orWhere('last_name', 'like', $search)
                        );
                    }
                },
                'students.saint:id,name', // chỉ lấy 2 cột cần thiết
            ])->find($this->selectedClassId);

            $this->students = $class
                ? $class->students
                : collect();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading students');
            $this->students = collect();
            session()->flash('error', 'Không thể tải danh sách học sinh');
        }
    }

    /**
     * FIX 1: loadSessions() chỉ load sessions — không gọi loadAttendanceRecords() bên trong nữa.
     * Caller tự gọi loadAttendanceRecords() sau khi loadSessions() xong.
     */
    protected function loadSessions(): void
    {
        if (!$this->selectedClassId) {
            $this->sessions     = [];
            $this->selectedDate = null;
            return;
        }

        if ($this->viewMode === 'mobile') {
            try {
                $this->sessions = AttendanceSession::where('class_id', $this->selectedClassId)
                    ->where('type', $this->attendanceType)
                    ->orderBy('date')
                    ->get(['id', 'date', 'status'])
                    ->map(fn($s) => [
                        'id'       => $s->id,
                        'dateStr'  => Carbon::parse($s->date)->format('Y-m-d'),
                        'fullDate' => Carbon::parse($s->date)->format('d/m'),
                        'dayName'  => $this->getVietnameseDayName(Carbon::parse($s->date)),
                        'locked'   => $s->status === AttendanceSession::STATUS_CLOSED,
                    ])->toArray();

                if (empty($this->sessions)) {
                    session()->flash('info', 'Chưa có buổi điểm danh nào');
                    return;
                }

                if (!$this->selectedDate) {
                    $this->autoSelectDateForMobile();
                }

                // FIX 1: đã bỏ $this->loadAttendanceRecords() khỏi đây
            } catch (\Exception $e) {
                $this->logError($e, 'Error loading sessions (mobile)');
                $this->sessions          = [];
                $this->selectedDate      = null;
                $this->attendanceRecords = [];
            }
            return;
        }

        try {
            $this->sessions = AttendanceSession::where('class_id', $this->selectedClassId)
                ->where('type', $this->attendanceType)
                ->when($this->selectedKy, fn($q) => $q->where('semester', $this->selectedKy))
                ->orderBy('date')
                ->get()
                ->map(function ($s) {
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

    /**
     * FIX 3: loadAttendanceRecords() tự gọi loadSessionIndicators() bên trong nếu mobile.
     * Không cần gọi loadSessionIndicators() rải rác ở các caller nữa.
     */
    protected function loadAttendanceRecords(): void
    {
        if (!$this->selectedClassId) {
            $this->attendanceRecords = [];
            return;
        }

        $this->loadAttendanceRecordsQuiet();

        if ($this->viewMode === 'mobile') {
            $this->loadSessionIndicators(); // FIX 3: tích hợp vào đây
        }

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

        $this->sessionHasRecord = [];
        foreach ($this->sessions as $session) {
            $count = collect($this->attendanceRecords)
                ->filter(fn($_, $key) => str_ends_with($key, '_' . $session['id']))
                ->count();
            if ($count > 0) {
                $this->sessionHasRecord[$session['dateStr']] = $count;
            }
        }
    }

    /**
     * Method duy nhất Alpine gọi để lưu.
     */
    public function saveFromClient(array $draft): void
    {
        if (empty($draft)) {
            session()->flash('warning', 'Không có dữ liệu để lưu');
            return;
        }

        foreach ($draft as $key => $item) {
            if (!preg_match('/^\d+_\d+$/', $key)) {
                session()->flash('error', 'Dữ liệu điểm danh không hợp lệ (key format)');
                return;
            }

            $status = $item['status'] ?? null;
            if (!is_int($status) || !in_array($status, [1, 2, 3])) {
                session()->flash('error', 'Trạng thái điểm danh không hợp lệ');
                return;
            }

            $note = $item['note'] ?? '';
            if (!is_string($note) || strlen($note) > 500) {
                session()->flash('error', 'Ghi chú không hợp lệ hoặc quá dài');
                return;
            }
        }

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
            $this->dispatchBrowserEvent('saving-attendance');

            $result = $this->attendanceService->saveBulkAttendance($drafts);

            if ($result['success']) {
                // FIX 3: loadAttendanceRecords() tự gọi loadSessionIndicators() bên trong
                $this->loadAttendanceRecords();

                session()->flash('message', $result['message'] ?? 'Đã lưu điểm danh thành công');

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
            $this->dispatchBrowserEvent('attendance-save-completed');
        }
    }

    public function switchType(int $type): void
    {
        if ($type === $this->attendanceType) return;
        $this->attendanceType = $type;
        $this->selectedDate   = null;
        $this->loadSessions();
        $this->loadAttendanceRecords(); // FIX 1+2: 1 lần ngoài, thay vì phân biệt desktop/mobile
    }

    // ==================== NOTE MODAL ====================

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

        $this->currentStudentId   = (int) $studentId;
        $this->currentSessionId   = (int) $sessionId;
        $this->currentStudentName = $student->full_name_with_saint
            ?? $student->full_name
            ?? 'Học sinh';

        $dbKey = $studentId . '_' . $sessionId;
        $this->attendanceNote = $this->attendanceRecords[$dbKey]['note'] ?? '';

        $this->showNoteModal = true;
    }

    public function saveAttendanceWithNote(): void
    {
        $this->validate(['attendanceNote' => 'nullable|string|max:500']);

        if (!$this->currentStudentId || !$this->currentSessionId) {
            session()->flash('error', 'Thiếu thông tin học sinh hoặc buổi học');
            return;
        }

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
        $this->showNoteModal      = false;
        $this->currentStudentId   = null;
        $this->currentSessionId   = null;
        $this->currentStudentName = '';
        $this->attendanceNote     = '';
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
                $this->selectedDate    = null;

                if ($this->selectedClassId) {
                    $this->selectedClassName = CatechismClass::where('id', $this->selectedClassId)
                        ->value('name') ?? '';

                    $this->loadStudents();
                    $this->loadSessions();
                    $this->loadAttendanceRecords(); // FIX 1+2: 1 lần ngoài
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
                    $this->loadAttendanceRecords(); // FIX 1+2: 1 lần ngoài
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
                $this->loadAttendanceRecords(); // FIX 1+2+3: 1 lần ngoài, tự gọi indicators bên trong
            }
        }
    }

    // ==================== HELPERS ====================

    protected function autoSelectDateForMobile(): void
    {
        if (empty($this->sessions)) return;

        $today    = Carbon::today()->format('Y-m-d');
        $sessions = collect($this->sessions)->sortBy('dateStr');

        $todaySession = $sessions->firstWhere('dateStr', $today);
        if ($todaySession) {
            $this->selectedDate = $today;
            return;
        }

        $prev = $sessions->last(fn($s) => $s['dateStr'] < $today);
        if ($prev) {
            $this->selectedDate = $prev['dateStr'];
            return;
        }

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
            ->orderBy('grade_level_id')
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

        $grid = [];
        foreach ($students as $student) {
            $grid[$student->id] = [];
            foreach ($this->sessions as $session) {
                $key = $student->id . '_' . $session['id'];
                $grid[$student->id][$session['id']] = $this->attendanceRecords[$key]['status'] ?? null;
            }
        }

        $stats = array_map(function ($session) use ($students) {
            $s = ['present' => 0, 'absentPermitted' => 0, 'absentNotPermitted' => 0];

            foreach ($students as $student) {
                $key    = $student->id . '_' . $session['id'];
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

        $statsAssoc = [];
        foreach ($this->sessions as $index => $session) {
            $statsAssoc[$session['dateStr']] = $stats[$index];
        }

        $layout = auth()->user()?->isCatechist()
            ? 'frontend.layout.catechist'
            : 'frontend.layout.main';

        return view('livewire.attendance-manager', [
            'students'       => $students,
            'parishId'       => $this->parishId,
            'attendanceGrid' => $grid,
            'sessionStats'   => $statsAssoc,
        ])->extends($layout)->section('content');
    }
}
