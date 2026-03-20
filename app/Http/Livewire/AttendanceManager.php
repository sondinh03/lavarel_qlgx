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

use function Psy\info;

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

    // public $viewMode     = 'desktop';
    public $selectedDate = null;

    // ==================== NOTE MODAL ====================

    public $showNoteModal      = false;
    public $currentStudentId   = null;
    public $currentSessionId   = null;
    public $currentStudentName = '';
    public $attendanceNote     = '';

    // ==================== DATA ====================

    public $students;
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
        // 'viewModeDetected' => 'setViewMode',
    ];

    // ==================== LIFECYCLE ====================

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount()
    {
        Log::info('🔴 mount() START', ['selectedClassId' => $this->selectedClassId]);

        $this->students = collect();
        parent::mount();
        $this->requireParishId();

        Log::info('🔴 mount() AFTER parent::mount()', ['selectedClassId' => $this->selectedClassId]);

        // if ($this->selectedClassId) {
        //     Log::info('🔴 mount() — nhánh if chạy → sắp gọi load lần 2');
        //     $class = CatechismClass::select('id', 'name', 'school_year_id', 'grade_level_id')
        //         ->find($this->selectedClassId);

        //     if ($class) {
        //         $this->selectedNamHoc    = $class->school_year_id;
        //         $this->selectedClassName = $class->name;

        //         $this->loadStudents();
        //         $this->loadSessions();
        //         $this->loadAttendanceRecords(); // FIX 1: gọi ngoài, không nhúng trong loadSessions()
        //     } else {
        //         $this->selectedClassId = null;
        //         session()->flash('warning', 'Lớp học không tồn tại');
        //     }
        // }
        Log::info('🔴 mount() END', ['selectedClassId' => $this->selectedClassId]);
        Log::info('   👥 Students loaded cuối mount', [
            'count' => $this->students->count(),
        ]);
    }

    protected function initializeUser(): void
    {
        parent::initializeUser();

        // Tận dụng auth đã được gọi trong parent
        $this->viewMode = auth()->user()?->isCatechist() ? 'mobile' : 'desktop';

        Log::info('👤 initializeUser()', [
            'viewMode' => $this->viewMode,
            'role'     => auth()->user()?->roles->pluck('name'),
        ]);
    }

    protected function loadInitialData(): void
    {
        Log::info('🟡 loadInitialData() START', ['selectedClassId' => $this->selectedClassId]);

        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }

        if ($this->selectedClassId) {
            $class = CatechismClass::select('id', 'name', 'school_year_id', 'grade_level_id')
                ->find($this->selectedClassId);

            if ($class) {
                $this->selectedNamHoc    = $class->school_year_id;
                $this->selectedClassName = $class->name;
            } else {
                // classId không hợp lệ → reset
                $this->selectedClassId = null;
                session()->flash('warning', 'Lớp học không tồn tại');
            }
        }

        if (!$this->selectedClassId && $this->selectedNamHoc) {
            $this->selectedClassId = $this->getDefaultClassId();

            if ($this->selectedClassId) {
                $class = CatechismClass::select('id', 'name')
                    ->find($this->selectedClassId);
                $this->selectedClassName = $class?->name ?? '';
            }
        }

        if ($this->selectedClassId) {
            Log::info('🟡 loadInitialData() — gọi load (1 lần duy nhất)');
            $this->loadStudents();
            $this->loadSessions();
            $this->loadAttendanceRecords();
        }
        Log::info('🟡 loadInitialData() END');
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
        Log::info('🟤 updatedSelectedClassId() called', [
            'selectedClassId' => $this->selectedClassId,
            'trace' => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3))
                ->map(fn($f) => ($f['class'] ?? '') . '::' . ($f['function'] ?? ''))
                ->implode(' → ')
        ]);

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
        Log::info('🟢 updatedSelectedDate() called', [
            'date'     => $this->selectedDate,
            'viewMode' => $this->viewMode,
        ]);
        if ($this->viewMode === 'mobile' && $this->selectedDate) {
            Log::info('🟢 updatedSelectedDate() — load records');
            $this->loadAttendanceRecords();
        }
    }

    public function updatedAttendanceType(): void
    {
        Log::info('🟤 updatedAttendanceType() called', [
            'attendanceType' => $this->attendanceType,
            'trace' => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3))
                ->map(fn($f) => ($f['class'] ?? '') . '::' . ($f['function'] ?? ''))
                ->implode(' → ')
        ]);

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
        Log::info('   📌 loadStudents() called', [
            'classId' => $this->selectedClassId,
            'trace'   => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4))
                ->map(fn($f) => ($f['class'] ?? '') . '::' . ($f['function'] ?? ''))
                ->implode(' → ')
        ]);
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

            Log::info('   👥 Students loaded', [
                'count' => $this->students->count(),
            ]);
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
        Log::info('   📌 loadSessions() called', [
            'classId' => $this->selectedClassId,
            'trace'   => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4))
                ->map(fn($f) => ($f['class'] ?? '') . '::' . ($f['function'] ?? ''))
                ->implode(' → ')
        ]);

        if (!$this->selectedClassId) {
            Log::info('đã vào if selectedClasId = null');
            $this->sessions     = [];
            $this->selectedDate = null;
            return;
        }

        if ($this->viewMode === 'mobile') {
            Log::info('đã vào view mobile');
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

                Log::info('session count: ' . count($this->sessions));

                if (empty($this->sessions)) {
                    session()->flash('info', 'Chưa có buổi điểm danh nào');
                    return;
                }

                if (!$this->selectedDate) {
                    Log::info('bắt đầu autoSelectDate');
                    $this->autoSelectDateForMobile();
                }
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

            Log::info('     📊 Sessions count: ' . count($this->sessions));

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
        Log::info('   🔍 loadAttendanceRecordsQuiet() called', [
            'classId'      => $this->selectedClassId,
            'type'         => $this->attendanceType,
            'viewMode'     => $this->viewMode,
            'selectedDate' => $this->selectedDate,
            'trace'        => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4))
                ->map(fn($f) => ($f['class'] ?? '') . '::' . ($f['function'] ?? ''))
                ->implode(' → ')
        ]);

        if (!$this->selectedClassId) {
            $this->attendanceRecords = [];
            return;
        }

        try {
            $query = AttendanceRecord::whereHas('session', function ($q) {
                $q->where('class_id', $this->selectedClassId)
                    ->where('type', $this->attendanceType);

                if ($this->viewMode === 'mobile' && $this->selectedDate) {
                    Log::info('   🔍 filter by date', ['date' => $this->selectedDate]);
                    $q->whereDate('date', $this->selectedDate);
                } else {
                    Log::info('   🔍 load ALL records (no date filter)');
                }
            });

            $this->attendanceRecords = $query->get()
                ->groupBy(fn($r) => $r->student_id . '_' . $r->session_id)
                ->map(fn($group) => [
                    'status' => $group->first()->status,
                    'note'   => $group->first()->note,
                ])
                ->toArray();

            Log::info('   🔍 loadAttendanceRecordsQuiet() DONE', [
                'record_count' => count($this->attendanceRecords),
            ]);
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
        Log::info('   📌 loadAttendanceRecords() called', [
            'classId' => $this->selectedClassId,
            'trace'   => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4))
                ->map(fn($f) => ($f['class'] ?? '') . '::' . ($f['function'] ?? ''))
                ->implode(' → ')
        ]);
        if (!$this->selectedClassId) {
            $this->attendanceRecords = [];
            return;
        }

        $this->loadAttendanceRecordsQuiet();

        if ($this->viewMode === 'mobile') {
            $this->loadSessionIndicators();
        }

        Log::info('      📊 Records count: ' . count($this->attendanceRecords));

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
        Log::info('💾 saveFromClient() START', [
            'draft_count' => count($draft),
            'keys'        => array_keys($draft),
        ]);

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

        Log::info('💾 saveFromClient() — gọi saveBulkAttendance', [
            'drafts_count' => count($drafts),
        ]);

        try {
            $this->dispatchBrowserEvent('saving-attendance');

            $result = $this->attendanceService->saveBulkAttendance($drafts);

            Log::info('💾 saveBulkAttendance() result', [
                'success' => $result['success'],
                'message' => $result['message'] ?? null,
            ]);

            if ($result['success']) {
                Log::info('💾 reload attendance records sau khi lưu');
                $this->loadAttendanceRecords();

                session()->flash('message', $result['message'] ?? 'Đã lưu điểm danh thành công');

                $this->dispatchBrowserEvent('attendance-saved', [
                    'records' => $this->attendanceRecords,
                ]);
                Log::info('💾 dispatch attendance-saved', [
                    'records_count' => count($this->attendanceRecords),
                ]);
            } else {
                session()->flash('error', $result['message']);
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error saving attendance');
            session()->flash('error', 'Có lỗi khi lưu điểm danh');
        } finally {
            $this->dispatchBrowserEvent('attendance-save-completed');
            Log::info('💾 saveFromClient() END');
        }
    }

    public function switchType(int $type): void
    {
        Log::info('🟠 switchType() START', [
            'old_type' => $this->attendanceType,
            'new_type' => $type,
        ]);
        if ($type === $this->attendanceType) {
            Log::info('🟠 switchType() — cùng type, bỏ qua');
            return;
        }

        $this->attendanceType = $type;
        $this->selectedDate   = null;

        Log::info('🟠 switchType() — bắt đầu load');
        $this->loadSessions();
        $this->loadAttendanceRecords();
        Log::info('🟠 switchType() END');
    }

    // ==================== NOTE MODAL ====================

    public function openNoteModal($studentId, $sessionId): void
    {
        Log::info('📝 openNoteModal() called', [
            'studentId' => $studentId,
            'sessionId' => $sessionId,
        ]);

        $session = collect($this->sessions)->firstWhere('id', (int) $sessionId);

        if (!$session || $session['locked']) {
            Log::info('📝 openNoteModal() — session locked hoặc không tồn tại');
            session()->flash('warning', 'Không thể điểm danh cho buổi này');
            return;
        }

        $student = \App\Models\StudentNew::find((int) $studentId);

        if (!$student) {
            Log::info('📝 openNoteModal() — student not found');
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

        Log::info('📝 openNoteModal() — mở modal', [
            'student'       => $this->currentStudentName,
            'existingNote'  => $this->attendanceNote ?: '(trống)',
            'existingStatus' => $this->attendanceRecords[$dbKey]['status'] ?? null,
        ]);

        $this->showNoteModal = true;
    }

    public function saveAttendanceWithNote(): void
    {
        Log::info('📝 saveAttendanceWithNote() called', [
            'studentId' => $this->currentStudentId,
            'sessionId' => $this->currentSessionId,
            'note'      => $this->attendanceNote,
        ]);
        $this->validate(['attendanceNote' => 'nullable|string|max:500']);

        if (!$this->currentStudentId || !$this->currentSessionId) {
            Log::info('📝 saveAttendanceWithNote() — thiếu studentId hoặc sessionId');
            session()->flash('error', 'Thiếu thông tin học sinh hoặc buổi học');
            return;
        }

        $key = $this->currentStudentId . '_' . $this->currentSessionId;

        Log::info('📝 saveAttendanceWithNote() — dispatch note-saved', [
            'key'    => $key,
            'status' => AttendanceRecord::STATUS_ABSENT_EXCUSED,
            'note'   => trim($this->attendanceNote),
        ]);

        $this->dispatchBrowserEvent('note-saved', [
            'key'    => $this->currentStudentId . '_' . $this->currentSessionId,
            'status' => AttendanceRecord::STATUS_ABSENT_EXCUSED,
            'note'   => trim($this->attendanceNote),
        ]);

        session()->flash('message', 'Đã ghi nhận vắng có phép');
        $this->closeNoteModal();
        Log::info('📝 saveAttendanceWithNote() END — draft updated in Alpine, chờ user bấm Lưu');
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
        Log::info('🟠 handleFilterChanged() called', [
            'filters' => $filters
        ]);

        if (!is_array($filters)) return;

        $namHocChanged = false;
        $khoiChanged   = false;
        $classChanged  = false;
        $kyChanged     = false;

        if (array_key_exists('namHoc', $filters)) {
            $newNamHoc = is_numeric($filters['namHoc']) ? (int) $filters['namHoc'] : null;
            if ($newNamHoc !== $this->selectedNamHoc) {
                $this->selectedNamHoc = $newNamHoc;
                $namHocChanged = true;
                Log::info('🟠 namHoc thay đổi', ['new' => $newNamHoc]);
            }
        }

        if (array_key_exists('khoi', $filters)) {
            $newKhoi = is_numeric($filters['khoi']) ? (int) $filters['khoi'] : null;
            $oldKhoi = is_numeric($this->selectedKhoi) ? (int) $this->selectedKhoi : null;

            if ($newKhoi !== $oldKhoi) {
                $this->selectedKhoi = $newKhoi;
                $khoiChanged = true;
                Log::info('🟠 khoi thay đổi', ['new' => $newKhoi]);
            }
        }

        if ($namHocChanged || $khoiChanged) {
            Log::info('🟠 clearAttendanceState() vì namHoc/khoi đổi');
            $this->clearAttendanceState();
            $this->resetPage();
            return;
        }

        if (array_key_exists('lop', $filters)) {
            $newClassId = is_numeric($filters['lop']) ? (int) $filters['lop'] : null;
            $oldClassId = is_numeric($this->selectedClassId) ? (int) $this->selectedClassId : null;

            Log::info('🟠 handleFilterChanged() — đổi lớp', [
                'old' => $oldClassId,
                'new' => $newClassId,
            ]);

            if ($newClassId !== $this->selectedClassId) {
                $this->selectedClassId = $newClassId;
                $this->selectedDate    = null;
                $classChanged = true;
                Log::info('🟠 lop thay đổi', ['new' => $newClassId]);
            }
        }

        if (array_key_exists('ky', $filters)) {
            $newKy = is_numeric($filters['ky']) ? (int) $filters['ky'] : null;
            $oldKy = is_numeric($this->selectedKy) ? (int) $this->selectedKy : null;
            if ($newKy !== $oldKy) {
                $this->selectedKy = $newKy;
                $kyChanged = true;
                Log::info('🟠 ky thay đổi', ['new' => $newKy]);
            }
        }

        // ── Load 1 lần duy nhất ở đây ─────────────────────────
        if ($classChanged || $kyChanged) {
            if (!$this->selectedClassId) {
                Log::info('🟠 classId null → clearAttendanceState');
                $this->clearAttendanceState();
            } else {
                if ($classChanged) {
                    $this->selectedClassName = CatechismClass::where('id', $this->selectedClassId)
                        ->value('name') ?? '';
                    Log::info('🟠 load students vì lớp đổi');
                    $this->loadStudents();
                }

                Log::info('🟠 load sessions + records (1 lần)');
                $this->loadSessions();
                $this->loadAttendanceRecords();
            }
        }

        Log::info('🟠 handleFilterChanged() END');
        $this->resetPage();
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
        Log::info('🟢 selectDate() called', [
            'old' => $this->selectedDate,
            'new' => $date,
        ]);

        $this->selectedDate = $date;

        if ($this->viewMode === 'mobile') {
            Log::info('🟢 selectDate() — load records');
            $this->loadAttendanceRecords();
        }

        Log::info('🟢 selectDate() END');
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
