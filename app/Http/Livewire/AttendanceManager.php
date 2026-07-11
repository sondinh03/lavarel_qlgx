<?php

namespace App\Http\Livewire;

use App\Exports\AttendanceExport;
use App\Http\Livewire\Base\BaseComponent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use App\Models\NamHoc;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceManager extends BaseComponent
{
    protected AttendanceService $attendanceService;

    protected $usePagination = false;

    // ==================== FILTERS ====================

    public $selectedNamHoc  = null;
    public $selectedKhoi    = '';
    public $selectedClassId = null;
    public $selectedKy      = '';
    public $attendanceType  = 1;

    // ==================== VIEW STATE ====================

    public $viewMode     = 'desktop';
    public $selectedDate = null;

    // ==================== DATA ====================

    public $students;
    public $sessions          = [];
    public $attendanceRecords = [];

    // ==================== CLASS NAME ====================

    public string $selectedClassName = '';

    // ==================== VALIDATION ====================

    protected $rules = [
        'selectedClassId' => 'nullable|integer|exists:classes,id',
        'attendanceType'  => 'required|integer|in:1,2',
        'search'          => 'nullable|string|max:255',
    ];

    protected $messages = [
        'selectedClassId.exists' => 'Lớp không tồn tại',
        'attendanceType.in'      => 'Loại điểm danh không hợp lệ',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return array_merge([
            'selectedClassId' => ['as' => 'classId', 'except' => null],
            'attendanceType'  => ['as' => 'type',    'except' => 1],
            'selectedDate'    => ['as' => 'date',    'except' => null],
            'selectedKy'      => ['as' => 'ky',      'except' => null],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh'          => 'handleRefresh',
        'filterChanged'    => 'handleFilterChanged',
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
    }

    protected function initializeUser(): void
    {
        parent::initializeUser();

        // Tận dụng auth đã được gọi trong parent
        $this->viewMode = auth()->user()?->usesCatechistLayout() ? 'mobile' : 'desktop';
    }

    protected function loadInitialData(): void
    {
        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }

        if (!$this->selectedClassId && $this->selectedNamHoc) {
            // Catechist → dùng defaultClassId từ BaseComponent
            // Không catechist → fallback lớp đầu tiên của năm học
            $this->selectedClassId = $this->defaultClassId
                ?? CatechismClass::where('school_year_id', $this->selectedNamHoc)
                ->orderBy('id')
                ->value('id');
        }

        if ($this->selectedClassId && !$this->assertCanMarkClass((int) $this->selectedClassId)) {
            $this->selectedClassId = $this->defaultClassId;
            if ($this->selectedClassId && !$this->assertCanMarkClass((int) $this->selectedClassId)) {
                $this->selectedClassId = null;
            }
            if (!$this->selectedClassId) {
                $this->emit('toast', 'warning', 'Bạn không có quyền điểm danh lớp này');
            }
        }

        if ($this->selectedClassId) {
            $class = CatechismClass::select('id', 'name', 'school_year_id', 'grade_level_id')
                ->find($this->selectedClassId);

            if ($class) {
                $this->selectedNamHoc    = (int) $class->school_year_id;
                $this->selectedClassName = $class->name;
            } else {
                // classId không hợp lệ → reset
                $this->selectedClassId = null;
                $this->emit('toast', 'warning', 'Lớp học không tồn tại');
            }
        }

        // Đồng bộ kỳ nếu URL chỉ có classId (FilterBar cũng detect kỳ — tránh lệch lần emit đầu)
        if ($this->selectedNamHoc && $this->selectedKy === null) {
            $this->selectedKy = $this->detectSemesterForNamHoc((int) $this->selectedNamHoc);
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
            ? (int) $this->selectedClassId : null;

        $this->selectedNamHoc = is_numeric($this->selectedNamHoc)
            ? (int) $this->selectedNamHoc : null;

        $this->selectedKhoi = is_numeric($this->selectedKhoi)
            ? (int) $this->selectedKhoi : null;

        $this->attendanceType = in_array((int) $this->attendanceType, [1, 2], true)
            ? (int) $this->attendanceType : 1;

        // Điểm danh: chỉ kỳ 1|2 (không dùng "Cả năm")
        $this->selectedKy = is_numeric($this->selectedKy)
            && in_array((int) $this->selectedKy, [1, 2], true)
            ? (int) $this->selectedKy : null;
    }

    protected function resetToDefaults(): void
    {
        parent::resetToDefaults();
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

        if ($this->selectedClassId && !$this->assertCanMarkClass((int) $this->selectedClassId)) {
            $this->selectedClassId = null;
            $this->emit('toast', 'error', 'Bạn không có quyền điểm danh lớp này');
            $this->clearAttendanceState();
            $this->resetPage();
            return;
        }

        if ($this->selectedClassId) {
            $this->selectedClassName = CatechismClass::where('id', $this->selectedClassId)
                ->value('name') ?? 'Chọn lớp';

            $this->loadStudents();
            $this->loadSessions();
            $this->loadAttendanceRecords();
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
        $this->loadAttendanceRecords();

        $this->resetPage();
    }

    // ==================== AUTHORIZATION ====================

    /**
     * Admin / GLV cùng xứ: điểm danh mọi lớp trong giáo xứ (parish scope trên CatechismClass).
     */
    protected function assertCanMarkClass(?int $classId): bool
    {
        if (!$classId) {
            return false;
        }

        $user = auth()->user();
        if (!$user) {
            return false;
        }

        if ($user->canManage() || $user->isSuperAdmin() || $user->isCatechist()) {
            return CatechismClass::where('id', $classId)->exists();
        }

        return false;
    }

    // ==================== STATE MANAGEMENT ====================

    protected function clearAttendanceState(): void
    {
        $this->selectedClassId   = null;
        $this->selectedClassName = '';
        $this->students          = collect();
        $this->sessions          = [];
        $this->attendanceRecords = [];
        $this->selectedDate      = null;

        $this->dispatchBrowserEvent('attendance-state-cleared');
    }

    public function handleRefresh(): void
    {
        if ($this->selectedClassId) {
            $this->loadStudents();
            $this->loadSessions();
            $this->loadAttendanceRecords();
        }
        $this->resetPage();
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
                        'students.parish_group_id',
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
                'students.parishGroup:id,name',
            ])->find($this->selectedClassId);


            $this->students = $class
                ? $class->students
                : collect();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading students');
            $this->students = collect();
            $this->emit('toast', 'error', 'Không thể tải danh sách học sinh');
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

        try {
            $query = AttendanceSession::where('class_id', $this->selectedClassId)
                ->where('type', $this->attendanceType);

            if (in_array((int) $this->selectedKy, [1, 2], true)) {
                $query->where('semester', (int) $this->selectedKy);
            }

            $sessions = $query->orderBy('date')
                ->get(['id', 'date', 'status', 'type', 'semester']);

            $this->sessions = $sessions->map(function ($s) {
                $date = Carbon::parse($s->date);
                $locked = in_array((int) $s->status, [
                    AttendanceSession::STATUS_CLOSED,
                    AttendanceSession::STATUS_CANCELLED,
                ], true);

                return [
                    'id'       => $s->id,
                    'dateStr'  => $date->format('Y-m-d'),
                    'fullDate' => $date->format('d/m'),
                    'dayName'  => $this->getVietnameseDayName($date),
                    'type'     => $s->type,
                    'status'   => $s->status,
                    'locked'   => $locked,
                ];
            })->toArray();

            if (empty($this->sessions)) {
                $this->emit('toast', 'info', 'Chưa có buổi điểm danh nào');
                if ($this->viewMode === 'mobile') {
                    $this->selectedDate = null;
                }
                return;
            }

            if ($this->viewMode === 'mobile' && !$this->selectedDate) {
                $this->autoSelectDateForMobile();
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

                if (in_array((int) $this->selectedKy, [1, 2], true)) {
                    $q->where('semester', (int) $this->selectedKy);
                }

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
            'context' => $this->getClientContext(),
        ]);
    }

    protected function getClientContext(): string
    {
        return implode('|', [
            'class:' . ($this->selectedClassId ?? 'none'),
            'type:' . ($this->attendanceType ?? 'none'),
            'mode:' . ($this->viewMode ?? 'none'),
            'date:' . ($this->selectedDate ?? 'all'),
            'ky:' . ($this->selectedKy ?? 'all'),
        ]);
    }

    /**
     * Method duy nhất Alpine gọi để lưu.
     */
    public function saveFromClient(array $draft): void
    {
        try {
            if (empty($draft)) {
                $this->emit('toast', 'warning', 'Không có dữ liệu để lưu');
                return;
            }

            if (!$this->selectedClassId) {
                $this->emit('toast', 'warning', 'Vui lòng chọn lớp trước khi lưu');
                return;
            }

            $classId = (int) $this->selectedClassId;

            if (!$this->assertCanMarkClass($classId)) {
                $this->emit('toast', 'error', 'Bạn không có quyền điểm danh lớp này');
                return;
            }

            $type = (int) $this->attendanceType;

            $allowedSessionIds = AttendanceSession::where('class_id', $classId)
                ->where('type', $type)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $allowedStudentIds = DB::table('students_class')
                ->where('class_id', $classId)
                ->where('status', 1)
                ->pluck('student_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $allowedSessionLookup = array_flip($allowedSessionIds);
            $allowedStudentLookup = array_flip($allowedStudentIds);

            foreach ($draft as $key => $item) {
                if (!is_string($key) || !preg_match('/^\d+_\d+$/', $key)) {
                    $this->emit('toast', 'error', 'Dữ liệu điểm danh không hợp lệ');
                    return;
                }

                [$studentId, $sessionId] = array_map('intval', explode('_', $key, 2));

                if (!isset($allowedStudentLookup[$studentId])) {
                    $this->emit('toast', 'error', 'Có học sinh không thuộc lớp đang điểm danh');
                    return;
                }

                if (!isset($allowedSessionLookup[$sessionId])) {
                    $this->emit('toast', 'error', 'Có buổi điểm danh không thuộc lớp / loại đang chọn');
                    return;
                }

                $status = isset($item['status']) && is_numeric($item['status'])
                    ? (int) $item['status']
                    : null;

                if (!in_array($status, [1, 2, 3], true)) {
                    $this->emit('toast', 'error', 'Trạng thái điểm danh không hợp lệ');
                    return;
                }

                $note = (string) ($item['note'] ?? '');
                if (mb_strlen($note) > 500) {
                    $this->emit('toast', 'error', 'Ghi chú không được vượt quá 500 ký tự');
                    return;
                }
            }

            $drafts = collect($draft)
                ->map(function ($item, $key) use ($type) {
                    [$studentId, $sessionId] = explode('_', $key);
                    return [
                        'student_id'     => (int) $studentId,
                        'session_id'     => (int) $sessionId,
                        'status'         => (int) $item['status'],
                        'note'           => (string) ($item['note'] ?? ''),
                        'attendanceType' => $type,
                    ];
                })
                ->values()
                ->toArray();

            $this->dispatchBrowserEvent('saving-attendance');

            $result = $this->attendanceService->saveBulkAttendance($drafts, $classId, $type);

            if ($result['success']) {
                $this->loadAttendanceRecords();

                $toastType = !empty($result['errors']) ? 'warning' : 'success';
                $this->emit('toast', $toastType, $result['message'] ?? 'Đã lưu điểm danh thành công');

                $this->dispatchBrowserEvent('attendance-saved', [
                    'records'   => $this->attendanceRecords,
                    'savedKeys' => $result['savedKeys'] ?? [],
                    'context'   => $this->getClientContext(),
                ]);
            } else {
                $this->emit('toast', 'error', $result['message']);
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error saving attendance');
            $this->emit('toast', 'error', 'Có lỗi khi lưu điểm danh');
        } finally {
            $this->dispatchBrowserEvent('attendance-save-completed');
        }
    }

    public function switchType(int $type): void
    {
        $type = in_array($type, [1, 2], true) ? $type : 1;

        if ($type === $this->attendanceType) {
            return;
        }

        $this->attendanceType = $type;
        $this->selectedDate   = null;

        $this->loadSessions();
        $this->loadAttendanceRecords();
    }

    public function exportAttendance()
    {
        if (!$this->selectedClassId) {
            $this->emit('toast', 'warning', 'Vui lòng chọn lớp trước khi xuất file');
            return;
        }

        if (!$this->assertCanMarkClass((int) $this->selectedClassId)) {
            $this->emit('toast', 'error', 'Bạn không có quyền xuất điểm danh lớp này');
            return;
        }

        $ky = is_numeric($this->selectedKy) ? (int) $this->selectedKy : null;

        $sessionsQuery = AttendanceSession::where('class_id', $this->selectedClassId)
            ->where('type', $this->attendanceType);

        if (in_array($ky, [1, 2], true)) {
            $sessionsQuery->where('semester', $ky);
        }

        if ($sessionsQuery->count() === 0) {
            $this->emit('toast', 'warning', 'Chưa có buổi điểm danh để xuất');
            return;
        }

        $className = CatechismClass::findOrFail($this->selectedClassId)->name;
        $typeSlug  = $this->attendanceType === 2 ? 'DiLe' : 'DiHoc';
        $kyLabel   = in_array($ky, [1, 2], true) ? 'HK' . $ky : 'CaNam';

        return response()->streamDownload(function () use ($ky) {
            echo \Maatwebsite\Excel\Facades\Excel::raw(
                new AttendanceExport($this->selectedClassId, $ky ?: null, $this->attendanceType),
                \Maatwebsite\Excel\Excel::XLSX
            );
        }, 'DiemDanh_' . $className . '_' . $kyLabel . '_' . $typeSlug . '_' . now()->format('dmY_His') . '.xlsx');
    }

    // ==================== EVENT HANDLERS ====================

    public function handleFilterChanged($filters): void
    {
        if (!is_array($filters)) {
            return;
        }

        $id = static function ($value): ?int {
            return is_numeric($value) ? (int) $value : null;
        };

        $namHocChanged = false;
        $khoiChanged   = false;
        $classChanged  = false;
        $kyChanged     = false;

        if (array_key_exists('namHoc', $filters)) {
            $newNamHoc = $id($filters['namHoc']);
            $oldNamHoc = $id($this->selectedNamHoc);
            if ($newNamHoc !== $oldNamHoc) {
                $this->selectedNamHoc = $newNamHoc;
                $namHocChanged = true;
            } else {
                $this->selectedNamHoc = $oldNamHoc;
            }
        }

        if (array_key_exists('khoi', $filters)) {
            $newKhoi = $id($filters['khoi']);
            $oldKhoi = $id($this->selectedKhoi);
            if ($newKhoi !== $oldKhoi) {
                $this->selectedKhoi = $newKhoi;
                $khoiChanged = true;
            } else {
                $this->selectedKhoi = $oldKhoi;
            }
        }

        if (array_key_exists('ky', $filters)) {
            $newKy = $id($filters['ky']);
            $oldKy = $id($this->selectedKy);
            if ($newKy !== $oldKy) {
                $this->selectedKy = $newKy;
                $kyChanged = true;
            } else {
                $this->selectedKy = $oldKy;
            }
        }

        $newClassId = array_key_exists('lop', $filters)
            ? $id($filters['lop'])
            : $id($this->selectedClassId);

        // Đổi năm/khối: chỉ clear lớp khi payload không mang lớp mới
        if ($namHocChanged || $khoiChanged) {
            if ($newClassId === null) {
                $this->clearAttendanceState();
                $this->resetPage();
                return;
            }

            // Cùng emit có lop → giữ/áp lớp, không return sớm (tránh mất classId)
            $this->selectedDate = null;
        }

        if (array_key_exists('lop', $filters)) {
            $oldClassId = $id($this->selectedClassId);

            if ($newClassId !== $oldClassId) {
                if ($newClassId && !$this->assertCanMarkClass($newClassId)) {
                    $this->emit('toast', 'error', 'Bạn không có quyền điểm danh lớp này');
                    $this->resetPage();
                    return;
                }

                $this->selectedClassId = $newClassId;
                $this->selectedDate    = null;
                $classChanged = true;
            } else {
                $this->selectedClassId = $oldClassId;
            }
        }

        if ($classChanged || $kyChanged || $namHocChanged || $khoiChanged) {
            if (!$this->selectedClassId) {
                $this->clearAttendanceState();
            } else {
                if ($classChanged || $namHocChanged || $khoiChanged) {
                    $this->selectedClassName = CatechismClass::where('id', $this->selectedClassId)
                        ->value('name') ?? '';
                    $this->loadStudents();
                }

                $this->loadSessions();
                $this->loadAttendanceRecords();
            }
        }

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
        $this->selectedDate = $date;

        if ($this->viewMode === 'mobile') {
            $this->loadAttendanceRecords();
        }
    }

    protected function getVietnameseDayName(Carbon $date): string
    {
        return ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'][$date->dayOfWeek];
    }

    protected function detectSemesterForNamHoc(int $namHocId): ?int
    {
        $namHoc = NamHoc::find($namHocId);
        if (!$namHoc) {
            return 1;
        }

        $today = now()->toDateString();

        if ($namHoc->start_date_one && $namHoc->end_date_one) {
            if (
                $today >= $namHoc->start_date_one->toDateString()
                && $today <= $namHoc->end_date_one->toDateString()
            ) {
                return 1;
            }
        }

        if ($namHoc->start_date_two && $namHoc->end_date_two) {
            if (
                $today >= $namHoc->start_date_two->toDateString()
                && $today <= $namHoc->end_date_two->toDateString()
            ) {
                return 2;
            }
        }

        if ($namHoc->end_date_two && $today > $namHoc->end_date_two->toDateString()) {
            return 2;
        }

        return 1;
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

        $layout = $this->viewMode === 'mobile'
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
