<?php

namespace App\Http\Livewire;

use App\Exports\AttendanceExport;
use App\Http\Livewire\Base\BaseComponent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use App\Models\ClassTeacher;
use App\Models\NamHoc;
use App\Models\User;
use App\Notifications\AttendanceSessionSummary;
use App\Services\AttendanceService;
use App\Services\SchoolYearResolver;
use App\Support\NotificationRecipients;
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
        $currentNamHocId = $this->getDefaultNamHocId();
        $isCatechistOnly = auth()->user()?->isCatechist() && !auth()->user()?->canManage();

        // GLV: luôn neo năm học đang mở. Admin: giữ năm từ filter/URL hoặc mặc định hiện tại.
        if ($isCatechistOnly || !$this->selectedNamHoc) {
            $this->selectedNamHoc = $currentNamHocId;
        }

        // classId URL/cũ phải thuộc đúng xứ + năm đang chọn
        if ($this->selectedClassId && !$this->isValidAttendanceClass((int) $this->selectedClassId)) {
            $this->selectedClassId = null;
        }

        if (!$this->selectedClassId && $this->selectedNamHoc) {
            $this->selectedClassId = $this->resolveDefaultClassForYear((int) $this->selectedNamHoc);
        }

        if ($this->selectedClassId && !$this->assertCanMarkClass((int) $this->selectedClassId)) {
            $this->selectedClassId = $this->resolveDefaultClassForYear((int) ($this->selectedNamHoc ?? 0));
            if ($this->selectedClassId && !$this->assertCanMarkClass((int) $this->selectedClassId)) {
                $this->selectedClassId = null;
            }
            if (!$this->selectedClassId) {
                $this->emit('toast', 'warning', 'Bạn không có quyền');
            }
        }

        if ($this->selectedClassId) {
            $class = CatechismClass::select('id', 'name', 'school_year_id', 'grade_level_id', 'parish_id')
                ->find($this->selectedClassId);

            if ($class && $this->isValidAttendanceClass((int) $class->id)) {
                $this->selectedNamHoc    = (int) $class->school_year_id;
                $this->selectedClassName = $class->name;
            } else {
                $this->selectedClassId = null;
                $this->emit('toast', 'warning', 'Lớp không tồn tại');
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

        // Điểm danh không dùng "Cả năm" (0) — ép về kỳ 1|2 (hoặc null để mount detect sau)
        $this->selectedKy = $this->normalizeAttendanceKy($this->selectedKy);
    }

    /**
     * Chấp nhận kỳ 1|2 hoặc sentinel 3 (hè / nghỉ giữa kỳ).
     * Giá trị 0 ("Cả năm") / lạ → kỳ/phase hiện tại theo năm học.
     */
    protected function normalizeAttendanceKy($ky): ?int
    {
        if (is_numeric($ky) && in_array((int) $ky, [1, 2, 3], true)) {
            return (int) $ky;
        }

        $namHocId = is_numeric($this->selectedNamHoc) ? (int) $this->selectedNamHoc : null;
        if ($namHocId) {
            return $this->detectSemesterForNamHoc($namHocId) ?? 1;
        }

        return null;
    }

    /**
     * Lọc phiên theo kỳ: 1|2 theo semester; 3 = hè/giữa kỳ (semester null hoặc ngoài khoảng HK).
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     */
    protected function applyAttendanceKyFilter($query): void
    {
        $ky = is_numeric($this->selectedKy) ? (int) $this->selectedKy : null;

        if (in_array($ky, [1, 2], true)) {
            $query->where('semester', $ky);

            return;
        }

        if ($ky !== 3) {
            return;
        }

        $namHoc = is_numeric($this->selectedNamHoc)
            ? NamHoc::find((int) $this->selectedNamHoc)
            : null;

        $operating = app(SchoolYearResolver::class)
            ->resolve($this->parishId ? (int) $this->parishId : null);

        if ($operating && $namHoc && $operating->id() === (int) $namHoc->id) {
            app(SchoolYearResolver::class)->applySessionPhaseFilter($query, $operating);

            return;
        }

        // Năm chọn tay khác năm vận hành — vẫn lọc buổi ngoài HK
        $query->where(function ($q) use ($namHoc) {
            $q->whereNull('semester');

            if ($namHoc?->end_date_two) {
                $q->orWhereDate('date', '>', $namHoc->end_date_two->toDateString());
            }

            if ($namHoc?->end_date_one && $namHoc?->start_date_two) {
                $q->orWhere(function ($inner) use ($namHoc) {
                    $inner->whereDate('date', '>', $namHoc->end_date_one->toDateString())
                        ->whereDate('date', '<', $namHoc->start_date_two->toDateString());
                });
            }
        });
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
            $this->emit('toast', 'error', 'Bạn không có quyền');
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
     * Admin / GLV cùng xứ: điểm danh mọi lớp thuộc giáo xứ (và năm đang chọn nếu có).
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

        if (!($user->canManage() || $user->isSuperAdmin() || $user->isCatechist())) {
            return false;
        }

        return $this->attendanceClassQuery($classId)->exists();
    }

    /**
     * Lớp hợp lệ để điểm danh: đúng xứ + đúng năm học đang chọn.
     */
    protected function isValidAttendanceClass(int $classId): bool
    {
        return $this->attendanceClassQuery($classId)->exists();
    }

    protected function attendanceClassQuery(int $classId)
    {
        return CatechismClass::where('id', $classId)
            ->when(
                $this->parishId,
                fn ($q) => $q->where('parish_id', $this->parishId)
            )
            ->when(
                $this->selectedNamHoc,
                fn ($q) => $q->where('school_year_id', (int) $this->selectedNamHoc)
            );
    }

    /**
     * Ưu tiên lớp phụ trách GLV (nếu có); fallback lớp active đầu tiên trong xứ/năm.
     */
    protected function resolveDefaultClassForYear(int $namHocId): ?int
    {
        if ($namHocId <= 0) {
            return null;
        }

        if (
            $this->defaultClassId
            && CatechismClass::where('id', $this->defaultClassId)
                ->where('school_year_id', $namHocId)
                ->when($this->parishId, fn ($q) => $q->where('parish_id', $this->parishId))
                ->exists()
        ) {
            return (int) $this->defaultClassId;
        }

        return CatechismClass::where('school_year_id', $namHocId)
            ->when($this->parishId, fn ($q) => $q->where('parish_id', $this->parishId))
            ->active()
            ->orderBy('id')
            ->value('id');
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
            $this->emit('toast', 'error', 'Không tải được danh sách học sinh');
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

            $this->applyAttendanceKyFilter($query);

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
                $this->emit('toast', 'info', 'Chưa có buổi điểm danh');
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

                $this->applyAttendanceKyFilter($q);

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
                $this->emit('toast', 'warning', 'Vui lòng chọn lớp');
                return;
            }

            $classId = (int) $this->selectedClassId;

            if (!$this->assertCanMarkClass($classId)) {
                $this->emit('toast', 'error', 'Bạn không có quyền');
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
                    $this->emit('toast', 'error', 'Dữ liệu không hợp lệ');
                    return;
                }

                [$studentId, $sessionId] = array_map('intval', explode('_', $key, 2));

                if (!isset($allowedStudentLookup[$studentId])) {
                    $this->emit('toast', 'error', 'Dữ liệu không hợp lệ');
                    return;
                }

                if (!isset($allowedSessionLookup[$sessionId])) {
                    $this->emit('toast', 'error', 'Dữ liệu không hợp lệ');
                    return;
                }

                $status = isset($item['status']) && is_numeric($item['status'])
                    ? (int) $item['status']
                    : null;

                if (!in_array($status, [1, 2, 3], true)) {
                    $this->emit('toast', 'error', 'Dữ liệu không hợp lệ');
                    return;
                }

                $note = (string) ($item['note'] ?? '');
                if (mb_strlen($note) > 500) {
                    $this->emit('toast', 'error', 'Ghi chú tối đa 500 ký tự');
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
                $this->emit('toast', $toastType, $result['message'] ?? 'Đã lưu điểm danh');

                try {
                    $this->notifyAttendanceSummary($drafts, $classId);
                } catch (\Throwable $e) {
                    Log::warning('Attendance summary notification failed', [
                        'class_id' => $classId,
                        'error'    => $e->getMessage(),
                    ]);
                }

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

    /**
     * Gửi tóm tắt điểm danh (1 thông báo/lần lưu) tới GLV lớp + admin giáo lý, trừ người đang lưu.
     *
     * @param  array<int, array{student_id: int, session_id: int, status: int, note: string, attendanceType: int}>  $drafts
     */
    protected function notifyAttendanceSummary(array $drafts, int $classId): void
    {
        if ($drafts === []) {
            return;
        }

        $class = CatechismClass::query()->find($classId);
        if (! $class) {
            return;
        }

        $statuses = collect($drafts)->pluck('status');
        $summary = [
            'class_name'         => $class->name ?? 'Lớp',
            'present'            => $statuses->filter(fn ($s) => (int) $s === AttendanceRecord::STATUS_PRESENT)->count(),
            'absent_excused'     => $statuses->filter(fn ($s) => (int) $s === AttendanceRecord::STATUS_ABSENT_EXCUSED)->count(),
            'absent_unexcused'   => $statuses->filter(fn ($s) => (int) $s === AttendanceRecord::STATUS_ABSENT_UNEXCUSED)->count(),
            'total'              => $statuses->count(),
        ];

        $actorId = auth()->id();

        $teacherUserIds = ClassTeacher::query()
            ->byClass($classId)
            ->when($class->school_year_id, fn ($q) => $q->byNamhoc($class->school_year_id))
            ->active()
            ->with('teacher:id,user_id')
            ->get()
            ->pluck('teacher.user_id')
            ->filter()
            ->unique()
            ->values();

        $recipients = User::query()
            ->whereIn('id', $teacherUserIds)
            ->get();

        if ($class->parish_id) {
            $admins = NotificationRecipients::parishRoles(
                (int) $class->parish_id,
                ['parish_admin', 'catechism_admin'],
                $actorId
            );
            $recipients = $recipients->merge($admins);
        }

        $recipients = $recipients
            ->filter(fn (User $u) => (int) $u->id !== (int) $actorId)
            ->unique('id');

        notify_users($recipients, new AttendanceSessionSummary($summary, $classId));
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
            $this->emit('toast', 'warning', 'Vui lòng chọn lớp');
            return;
        }

        if (!$this->assertCanMarkClass((int) $this->selectedClassId)) {
            $this->emit('toast', 'error', 'Bạn không có quyền');
            return;
        }

        $ky = is_numeric($this->selectedKy) ? (int) $this->selectedKy : null;

        $sessionsQuery = AttendanceSession::where('class_id', $this->selectedClassId)
            ->where('type', $this->attendanceType);

        $this->applyAttendanceKyFilter($sessionsQuery);

        if ($sessionsQuery->count() === 0) {
            $this->emit('toast', 'warning', 'Chưa có buổi để xuất');
            return;
        }

        $className = CatechismClass::findOrFail($this->selectedClassId)->name;
        $typeSlug  = $this->attendanceType === 2 ? 'DiLe' : 'DiHoc';
        $kyLabel   = match ($ky) {
            1, 2 => 'HK' . $ky,
            3 => 'He',
            default => 'CaNam',
        };

        $exportKy = in_array($ky, [1, 2, 3], true) ? $ky : null;

        return response()->streamDownload(function () use ($exportKy) {
            echo \Maatwebsite\Excel\Facades\Excel::raw(
                new AttendanceExport($this->selectedClassId, $exportKy, $this->attendanceType),
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
            $newKy = $this->normalizeAttendanceKy($filters['ky']);
            $oldKy = $this->normalizeAttendanceKy($this->selectedKy);
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
                    $this->emit('toast', 'error', 'Bạn không có quyền');
                    $this->resetPage();
                    return;
                }

                // Bỏ lớp không thuộc năm/xứ đang chọn (tránh classId sai khi trùng tên)
                if ($newClassId && !$this->isValidAttendanceClass($newClassId)) {
                    $this->emit('toast', 'warning', 'Lớp không thuộc năm học đang chọn');
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
        $allowed = collect($this->sessions)->pluck('dateStr')->all();
        if (! in_array($date, $allowed, true)) {
            return;
        }

        $this->selectedDate = $date;

        if ($this->viewMode === 'mobile') {
            $this->loadAttendanceRecords();
        }
    }

    protected function getVietnameseDayName(Carbon $date): string
    {
        return [
            'Chúa Nhật',
            'Thứ Hai',
            'Thứ Ba',
            'Thứ Tư',
            'Thứ Năm',
            'Thứ Sáu',
            'Thứ Bảy',
        ][$date->dayOfWeek];
    }

    /**
     * Kỳ/phase theo năm: 1|2 trong HK; hè hoặc nghỉ giữa kỳ → sentinel 3 (không ép HK2).
     */
    protected function detectSemesterForNamHoc(int $namHocId): ?int
    {
        $operating = app(SchoolYearResolver::class)
            ->resolve($this->parishId ? (int) $this->parishId : null);

        if ($operating && $operating->id() === $namHocId) {
            return $operating->semester ?? 3;
        }

        $namHoc = NamHoc::find($namHocId);
        if (! $namHoc) {
            return 1;
        }

        $semester = app(SchoolYearResolver::class)->semesterForDate($namHoc, now());

        return $semester ?? 3;
    }

    protected function getDefaultNamHocId(): ?int
    {
        return app(SchoolYearResolver::class)
            ->resolveId($this->parishId ? (int) $this->parishId : null);
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
