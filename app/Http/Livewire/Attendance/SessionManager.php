<?php

namespace App\Http\Livewire\Attendance;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use App\Models\GradeLevel;
use App\Models\NamHoc;
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
    // ==================== FILTERS ====================

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
        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
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
        $this->currentNamHoc   = null;
        $this->search          = '';
        $this->resetPage();
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

    protected function getSessionsPaginated(): LengthAwarePaginator
    {
        try {
            return $this->getCurrentSessionsQuery()
                ->paginate($this->perPage);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading sessions', [
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
            'statusLabel' => $this->getStatusLabel($session->status),
            'statusClass' => $this->getStatusClass($session->status),
            'locked'      => $session->status === AttendanceSession::STATUS_CLOSED,
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
                $this->currentNamHoc   = null;
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
        $this->authorize('create', AttendanceSession::class);

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

    public function toggleStatus(int $id): void
    {
        $this->authorize('update', AttendanceSession::class);

        try {
            $session = AttendanceSession::findOrFail($id);

            $newStatus = $session->status === AttendanceSession::STATUS_OPENING
                ? AttendanceSession::STATUS_CLOSED
                : AttendanceSession::STATUS_OPENING;

            $session->update(['status' => $newStatus]);

            $label = $newStatus === AttendanceSession::STATUS_CLOSED
                ? 'Đã khóa phiên điểm danh'
                : 'Đã mở lại phiên điểm danh';

            $this->emit('toast', 'success', $label);
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling session status', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi thay đổi trạng thái');
        }
    }

    public function delete(int $id): void
    {
        $this->authorize('delete', AttendanceSession::class);

        try {
            $session = AttendanceSession::findOrFail($id);

            if ($session->records()->whereNotNull('status')->exists()) {
                $this->emit('toast', 'error', 'Không thể xóa phiên đã có dữ liệu điểm danh');
                return;
            }

            DB::beginTransaction();
            $session->delete();
            DB::commit();

            $this->emit('toast', 'success', 'Đã xóa phiên điểm danh');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting session', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa phiên điểm danh');
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

    protected function filterValidDates(array $dates): array
    {
        if (!$this->currentNamHoc) {
            return [];
        }

        $hk1Start = $this->currentNamHoc->start_date_one;
        $hk1End   = $this->currentNamHoc->end_date_one;
        $hk2Start = $this->currentNamHoc->start_date_two;
        $hk2End   = $this->currentNamHoc->end_date_two;

        // Không có khoảng nào → cho qua hết
        if (!$hk1Start && !$hk1End && !$hk2Start && !$hk2End) {
            return $dates;
        }

        return array_values(array_filter($dates, function ($date) use ($hk1Start, $hk1End, $hk2Start, $hk2End) {
            $carbon = Carbon::parse($date);
            $inHk1  = $hk1Start && $hk1End && $carbon->between($hk1Start, $hk1End);
            $inHk2  = $hk2Start && $hk2End && $carbon->between($hk2Start, $hk2End);
            return $inHk1 || $inHk2;
        }));
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
            AttendanceSession::STATUS_OPENING   => 'bg-green-50/80 text-green-700',
            AttendanceSession::STATUS_CLOSED    => 'bg-slate-100/80 text-slate-600',
            AttendanceSession::STATUS_CANCELLED => 'bg-red-50/80 text-red-700',
            default                             => 'bg-slate-50/80 text-slate-500',
        };
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
        $paginator = $this->getSessionsPaginated();

        // Map model → array hiển thị (giữ nguyên cấu trúc blade cũ)
        $sessions = $paginator->through(fn($session) => $this->mapSession($session));

        return view('livewire.attendance.session-manager', [
            'parishId'  => $this->parishId,
            'sessions'  => $sessions,   // LengthAwarePaginator (đã map)
            'total'     => $paginator->total(),
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
