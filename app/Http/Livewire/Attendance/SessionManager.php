<?php

namespace App\Http\Livewire\Attendance;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use App\Models\NamHoc;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Component quản lý Phiên điểm danh (CRUD)
 */
class SessionManager extends BaseComponent
{
    // ==================== FILTERS ====================

    /** @var int|null Selected năm học ID */
    public $selectedNamHoc = null;

    /** @var int|string Selected khối ('' = all) */
    public $selectedKhoi = '';

    /** @var int|null Selected lớp */
    public $selectedClassId = null;

    /** @var string Phạm vi tạo: class, khoi, parish */
    public $scope = 'class';

    // ==================== FORM STATE ====================
    /** @var int|null ID session đang edit (null = create) */
    public $editingId = null;

    // ==================== FORM FIELDS ====================

    /** @var int Loại điểm danh (1: học, 2: lễ) */
    public $type = 1;

    /** @var string Tiêu đề phiên */
    public $title = '';

    /** @var string|null Ngày bắt đầu tạo */
    public $startDate = null;

    /** @var string|null Ngày kết thúc tạo */
    public $endDate = null;

    /** @var array Các ngày cụ thể được chọn */
    public $selectedDates = [];

    /** @var string Chế độ tạo: single, weekly, custom */
    public $createMode = 'single';

    /** @var array Các ngày trong tuần (0=CN, 1=T2...) */
    public $weekDays = [];

    /** @var string|null Start time */
    public $startTime = null;

    /** @var string|null End time */
    public $endTime = null;

    // ==================== DATA ====================

    /** @var \Illuminate\Support\Collection */
    public $sessions;

    /** @var object|null Năm học hiện tại */
    public $currentNamHoc = null;

    /** @var bool Không dùng pagination */
    protected $usePagination = false;

    // ==================== VALIDATION ====================

    protected $rules = [
        'type'           => 'required|integer|in:1,2',
        'title'          => 'nullable|string|max:255',
        'createMode'     => 'required|string|in:single,weekly,custom',
        'weekDays'       => 'required_if:createMode,weekly|array',
        'weekDays.*'     => 'integer|between:0,6',
        'selectedDates'  => 'required_if:createMode,custom|array',
        'startTime'      => 'nullable|date_format:H:i',
        'endTime'        => 'nullable|date_format:H:i|after:startTime',
        'scope'          => 'required|string|in:class,khoi,parish',
    ];

    protected $messages = [
        'type.required'              => 'Vui lòng chọn loại điểm danh',
        'type.in'                    => 'Loại điểm danh không hợp lệ',
        'weekDays.required_if'       => 'Vui lòng chọn ít nhất 1 ngày trong tuần',
        'selectedDates.required_if'  => 'Vui lòng chọn ít nhất 1 ngày',
        'endTime.after'              => 'Giờ kết thúc phải sau giờ bắt đầu',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return array_merge([
            'selectedNamHoc'  => ['as' => 'namHoc', 'except' => null],
            'selectedKhoi'    => ['as' => 'khoi', 'except' => ''],
            'selectedClassId' => ['as' => 'classId', 'except' => null],
            'scope' => ['as' => 'scope', 'except' => 'class'],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh'        => 'handleRefresh',
        'filterChanged'  => 'handleFilterChanged',
        'sessionCreated' => 'loadSessions',
        'sessionUpdated' => 'loadSessions',
    ];

    // ==================== LIFECYCLE ====================

    public function mount()
    {
        // Khởi tạo collection trước parent::mount()
        $this->sessions = collect();

        parent::mount();

        $this->requireManager();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }

        if ($this->selectedClassId) {
            $this->loadClassInfo();
            $this->startDate = $this->currentNamHoc?->start_date_one
                ? $this->currentNamHoc->start_date_one->format('Y-m-d')
                : null;
            $this->endDate = $this->currentNamHoc?->end_date_one
                ? $this->currentNamHoc->end_date_one->format('Y-m-d')
                : null;
            $this->loadSessions();
        }
    }

    // ==================== SANITIZE ====================

    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        $this->selectedNamHoc = is_numeric($this->selectedNamHoc)
            ? (int) $this->selectedNamHoc
            : null;

        $this->selectedKhoi = is_numeric($this->selectedKhoi)
            ? (int) $this->selectedKhoi
            : '';

        // ✅ Fix: đồng nhất kiểu với AttendanceManager (null thay vì '')
        $this->selectedClassId = is_numeric($this->selectedClassId)
            ? (int) $this->selectedClassId
            : null;
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
            $this->selectedKhoi   = $class->grade_level_id;
            $this->currentNamHoc  = $class->schoolYear;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading class info');
            $this->emit('toast',  'error', 'Không tìm thấy lớp học');
        }
    }

    public function loadSessions(): void
    {
        if (!$this->selectedClassId) {
            $this->sessions = collect();
            return;
        }

        try {
            $query = AttendanceSession::where('class_id', $this->selectedClassId)
                ->orderByDesc('date')
                ->orderByDesc('type');

            if (!empty(trim($this->search))) {
                $search = '%' . trim($this->search) . '%';
                $query->where('title', 'like', $search);
            }

            $this->sessions = $query->get()->map(function ($session) {
                $stats = $session->getStatistics();
                return [
                    'id'          => $session->id,
                    'dateStr'     => $session->date->format('Y-m-d'),
                    'fullDate'    => $session->date->format('d/m/Y'),
                    'dayName'     => $this->getVietnameseDayName($session->date),
                    'type'        => $session->type,
                    'typeLabel'   => $session->type == AttendanceSession::TYPE_CLASS ? 'Đi học' : 'Đi lễ',
                    'title'       => $session->title,
                    'status'      => $session->status,
                    'statusLabel' => $this->getStatusLabel($session->status),
                    'statusClass' => $this->getStatusClass($session->status),
                    'locked'      => $session->status === AttendanceSession::STATUS_CLOSED,
                    'start_time'  => $session->start_time?->format('H:i'),
                    'end_time'    => $session->end_time?->format('H:i'),
                    'stats'       => $stats,
                ];
            });
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading sessions');
            $this->sessions = collect();
        }
    }

    // ==================== EVENT HANDLERS ====================

    public function handleFilterChanged($filters): void
    {
        if (!is_array($filters)) {
            return;
        }

        $namHocChanged = false;

        if (array_key_exists('namHoc', $filters)) {
            $newNamHoc = is_numeric($filters['namHoc']) ? (int) $filters['namHoc'] : null;
            if ($newNamHoc !== $this->selectedNamHoc) {
                $this->selectedNamHoc = $newNamHoc;
                $namHocChanged = true;
            }
        }

        if (array_key_exists('khoi', $filters)) {
            $this->selectedKhoi = is_numeric($filters['khoi'])
                ? (int) $filters['khoi']
                : '';
        }

        if (array_key_exists('lop', $filters)) {
            $newClassId = is_numeric($filters['lop']) ? (int) $filters['lop'] : null;

            if ($newClassId !== $this->selectedClassId) {
                $this->selectedClassId = $newClassId;

                if ($this->selectedClassId) {
                    $this->loadClassInfo();
                    $this->loadSessions();
                } else {
                    $this->sessions     = collect();
                    $this->currentNamHoc = null;
                }
            }
        }

        // Reset class khi đổi năm học
        if ($namHocChanged) {
            $this->selectedClassId = null;
            $this->sessions        = collect();
            $this->currentNamHoc   = null;
        }
    }

    // ==================== CRUD ACTIONS ====================

    public function create(): void
    {
        $this->authorize('create', AttendanceSession::class);

        // if (!$this->selectedClassId) {
        //     $this->emit('toast',  'warning', 'Vui lòng chọn lớp trước');
        //     return;
        // }

        $this->currentNamHoc = NamHoc::find($this->selectedNamHoc);

        if (!$this->currentNamHoc) {
            $this->emit('toast',  'warning', 'Không tìm thấy thông tin năm học');
            return;
        }

        $this->resetForm();
        $this->emit('openModal');

        $today          = Carbon::today();
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
    }

    protected function resolveClassIds(): array
    {
        return match ($this->scope) {
            'khoi' => CatechismClass::where('grade_level_id', $this->selectedKhoi)
                ->where('school_year_id', $this->selectedNamHoc)
                ->pluck('id')->toArray(),

            'parish' => CatechismClass::where('school_year_id', $this->selectedNamHoc)
                ->pluck('id')->toArray(),

            default => $this->selectedClassId ? [$this->selectedClassId] : [],
        };
    }

    public function save(): void
    {
        $this->authorize('create', AttendanceSession::class);

        if ($this->createMode === 'weekly' && empty($this->weekDays)) {
            $this->emit('toast',  'error', 'Vui lòng chọn ít nhất 1 ngày trong tuần');
            return;
        }

        if ($this->createMode === 'custom' && empty($this->selectedDates)) {
            $this->emit('toast',  'error', 'Vui lòng chọn ít nhất 1 ngày');
            return;
        }

        if (!$this->startDate) {
            $this->emit('toast',  'error', 'Vui lòng chọn ngày bắt đầu');
            return;
        }

        $this->validate($this->rules, $this->messages);

        try {
            DB::beginTransaction();

            $classIds   = $this->resolveClassIds();
            $dates      = $this->generateDates();
            $validDates = $this->filterValidDates($dates);

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

            if (empty($validDates)) {
                $this->emit('toast', 'warning', 'Tất cả các ngày đều nằm ngoài khoảng thời gian năm học.');
                DB::rollBack();
                return;
            }

            $created = 0;
            $skipped = 0;

            foreach ($classIds as $classId) {
                foreach ($validDates as $date) {
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
                            'end_time'   => $this->endTime ? Carbon::parse($this->endTime) : null,
                            'note'       => $this->title ?: null,
                        ]);
                        $created++;
                    } catch (\Exception $e) {
                        $this->logError($e, 'Error creating attendance session', [
                            'class_id' => $classId,
                            'date'     => $date,
                        ]);
                        continue;
                    }
                }
            }

            DB::commit();

            $classCount = count($classIds);
            $message = "Đã tạo {$created} phiên cho {$classCount} lớp";
            if ($skipped > 0) {
                $message .= " ({$skipped} phiên đã tồn tại, bỏ qua)";
            }

            $this->emit('toast',  'message', $message);
            $this->resetForm();
            $this->loadSessions();
            $this->emit('sessionCreated');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error creating sessions', [
                'class_id' => $this->selectedClassId,
                'mode'     => $this->createMode,
            ]);
            $this->emit('toast',  'error', 'Có lỗi khi tạo phiên điểm danh. Vui lòng thử lại.');
        }
    }

    public function toggleStatus(int $id): void
    {
        $this->authorize('update', AttendanceSession::class);

        try {
            // ✅ Fix: bỏ filter parish_id vì AttendanceSession không có cột này
            $session = AttendanceSession::where('class_id', $this->selectedClassId)
                ->findOrFail($id);

            $newStatus = $session->status === AttendanceSession::STATUS_OPENING
                ? AttendanceSession::STATUS_CLOSED
                : AttendanceSession::STATUS_OPENING;

            $session->update(['status' => $newStatus]);

            $message = $newStatus === AttendanceSession::STATUS_CLOSED
                ? 'Đã khóa phiên điểm danh'
                : 'Đã mở lại phiên điểm danh';

            $this->emit('toast',  'message', $message);
            $this->loadSessions();
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling session status', ['id' => $id]);
            $this->emit('toast',  'error', 'Có lỗi khi thay đổi trạng thái');
        }
    }

    public function delete(int $id): void
    {
        $this->authorize('delete', AttendanceSession::class);

        try {
            $session = AttendanceSession::where('class_id', $this->selectedClassId)
                ->findOrFail($id);

            $hasRecords = $session->records()->whereNotNull('status')->exists();

            if ($hasRecords) {
                $this->emit('toast', 'error', 'Không thể xóa phiên đã có dữ liệu điểm danh');
                return; // return sạch, không có transaction nào mở
            }

            DB::beginTransaction();
            $session->delete();
            DB::commit();

            $this->emit('toast', 'message', 'Đã xóa phiên điểm danh');
            $this->loadSessions();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting session', ['id' => $id]);
            $this->emit('toast',  'error', 'Có lỗi khi xóa phiên điểm danh');
        }
    }

    // ==================== HELPER METHODS ====================

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

    protected function filterValidDates(array $dates): array
    {
        if (!$this->currentNamHoc) {
            return [];
        }

        $hk1Start = $this->currentNamHoc->start_date_one;
        $hk1End   = $this->currentNamHoc->end_date_one;
        $hk2Start = $this->currentNamHoc->start_date_two;
        $hk2End   = $this->currentNamHoc->end_date_two;

        // Không có date range nào thì cho qua hết
        if (!$hk1Start && !$hk1End && !$hk2Start && !$hk2End) {
            return $dates;
        }

        return array_values(array_filter($dates, function ($date) use ($hk1Start, $hk1End, $hk2Start, $hk2End) {
            $carbon = Carbon::parse($date);

            $inHk1 = $hk1Start && $hk1End && $carbon->between($hk1Start, $hk1End);
            $inHk2 = $hk2Start && $hk2End && $carbon->between($hk2Start, $hk2End);

            return $inHk1 || $inHk2;
        }));
    }

    /**
     * Lấy học kỳ của một ngày cụ thể (1, 2, hoặc null nếu ngoài khoảng)
     */
    protected function getSemesterForDate(string|Carbon $date): ?int
    {
        if (!$this->currentNamHoc) {
            return null;
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        if ($this->currentNamHoc->start_date_one && $this->currentNamHoc->end_date_one) {
            if ($carbon->between($this->currentNamHoc->start_date_one, $this->currentNamHoc->end_date_one)) {
                return 1;
            }
        }

        if ($this->currentNamHoc->start_date_two && $this->currentNamHoc->end_date_two) {
            if ($carbon->between($this->currentNamHoc->start_date_two, $this->currentNamHoc->end_date_two)) {
                return 2;
            }
        }

        return null;
    }

    /**
     * Lấy học kỳ hiện tại (dựa theo ngày hôm nay)
     */
    protected function getCurrentSemester(): ?int
    {
        return $this->getSemesterForDate(Carbon::today());
    }

    protected function getDefaultNamHocId(): ?int
    {
        return NamHoc::where('parish_id', $this->parishId)
            ->where('status', true)
            ->orderByDesc('name')
            ->value('id');
    }

    protected function getVietnameseDayName(Carbon $date): string
    {
        return ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'][$date->dayOfWeek];
    }

    protected function getStatusLabel(int $status): string
    {
        return match ($status) {
            AttendanceSession::STATUS_OPENING   => 'Đang mở',
            AttendanceSession::STATUS_CLOSED    => 'Đã khóa',
            AttendanceSession::STATUS_CANCELLED => 'Đã hủy',
            default                             => 'Không xác định',
        };
    }

    protected function getStatusClass(int $status): string
    {
        return match ($status) {
            AttendanceSession::STATUS_OPENING   => 'bg-green-100 text-green-700',
            AttendanceSession::STATUS_CLOSED    => 'bg-slate-200 text-slate-600',
            AttendanceSession::STATUS_CANCELLED => 'bg-red-100 text-red-700',
            default                             => 'bg-slate-100 text-slate-500',
        };
    }

    public function closeModal(): void
    {
        // $this->showForm = false;
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
            'scope', 
        ]);

        $this->type       = 1;
        $this->createMode = 'single';
        // $this->showForm   = false;
        $this->resetValidation();
        $this->emit('closeModal');
    }

    // ==================== COMPUTED PROPERTIES ====================

    public function getSelectedClassNameProperty(): string
    {
        if (!$this->selectedClassId) {
            return 'Chọn lớp';
        }

        return CatechismClass::where('id', $this->selectedClassId)->value('name') ?? 'Chọn lớp';
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.attendance.session-manager', [
            'parishId' => $this->parishId,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
