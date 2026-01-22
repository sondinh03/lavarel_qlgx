<?php

namespace App\Http\Livewire\Attendance;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\AttendanceSession;
use App\Models\Lop;
use App\Models\NamHoc;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Component quản lý Phiên điểm danh (CRUD)
 * 
 * Features:
 * - Tạo nhiều phiên cùng lúc (theo tuần/tháng/chọn ngày cụ thể)
 * - Filter theo năm học, khối, lớp
 * - Chỉ tạo trong khoảng start_date - end_date của năm học
 * - Toggle status (mở/khóa)
 * - Delete sessions
 */
class SessionManager extends BaseComponent
{
    // ==================== FILTERS ====================

    /** @var int|null Selected năm học ID */
    public $selectedNamHoc = null;

    /** @var int|string Selected khối ('' = all) */
    public $selectedKhoi = '';

    /** @var int|string Selected lớp ('' = all) */
    public $selectedClassId = '';

    // ==================== FORM STATE ====================

    /** @var bool Hiển thị modal form */
    public $showForm = false;

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

    /** @var \Illuminate\Support\Collection Sessions data */
    public $sessions;

    /** @var object|null Năm học hiện tại info */
    public $currentNamHoc = null;

    // ==================== VALIDATION ====================

    protected $rules = [
        // 'selectedClassId' => 'required|integer|exists:lop,id',
        'type' => 'required|integer|in:1,2',
        'title' => 'nullable|string|max:255',
        'createMode' => 'required|string|in:single,weekly,custom',
        // 'startDate' => 'required|date',
        // 'endDate' => 'nullable|date|after_or_equal:startDate',
        'weekDays' => 'required_if:createMode,weekly|array',
        'weekDays.*' => 'integer|between:0,6',
        'selectedDates' => 'required_if:createMode,custom|array',
        'startTime' => 'nullable|date_format:H:i',
        'endTime' => 'nullable|date_format:H:i|after:startTime',
    ];

    protected $messages = [
        'selectedClassId.required' => 'Vui lòng chọn lớp',
        'selectedClassId.exists' => 'Lớp không tồn tại',
        'type.required' => 'Vui lòng chọn loại điểm danh',
        'type.in' => 'Loại điểm danh không hợp lệ',
        'startDate.required' => 'Vui lòng chọn ngày bắt đầu',
        'startDate.date' => 'Ngày bắt đầu không hợp lệ',
        'endDate.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
        'weekDays.required_if' => 'Vui lòng chọn ít nhất 1 ngày trong tuần',
        'selectedDates.required_if' => 'Vui lòng chọn ít nhất 1 ngày',
        'endTime.after' => 'Giờ kết thúc phải sau giờ bắt đầu',
    ];

    /** @var bool Không dùng pagination */
    protected $usePagination = false;

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return array_merge([
            'selectedNamHoc' => ['as' => 'namHoc', 'except' => null],
            'selectedKhoi'   => ['as' => 'khoi', 'except' => ''],
            'selectedClassId' => ['as' => 'classId', 'except' => ''],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh' => 'handleRefresh',
        'filterChanged' => 'handleFilterChanged',
        'sessionCreated' => 'loadSessions',
        'sessionUpdated' => 'loadSessions',
    ];

    // ==================== LIFECYCLE ====================

    public function mount()
    {
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

    /**
     * Get năm học mặc định (năm active mới nhất)
     */
    protected function getDefaultNamHocId(): ?int
    {
        return NamHoc::ofParish($this->parishId)
            ->active()
            ->orderByDesc('name')
            ->value('id');
    }

    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        $this->selectedNamHoc = is_numeric($this->selectedNamHoc)
            ? (int) $this->selectedNamHoc
            : null;

        $this->selectedKhoi = is_numeric($this->selectedKhoi)
            ? (int) $this->selectedKhoi
            : '';

        $this->selectedClassId = is_numeric($this->selectedClassId)
            ? (int) $this->selectedClassId
            : '';
    }

    // ==================== DATA LOADING ====================

    protected function loadClassInfo(): void
    {
        if (!$this->selectedClassId) {
            return;
        }

        try {
            $lop = Lop::with('blockRelation', 'schoolYear')
                ->ofParish($this->parishId)
                ->findOrFail($this->selectedClassId);

            $this->selectedNamHoc = $lop->schoolyear;
            $this->selectedKhoi = $lop->block;

            // Load năm học info để lấy date range
            $this->currentNamHoc = NamHoc::find($this->selectedNamHoc);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading class info');
            session()->flash('error', 'Không tìm thấy lớp học');
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

            if (!empty($this->search)) {
                $search = '%' . trim($this->search) . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', $search);
                });
            }

            $this->sessions = $query->get()->map(function ($session) {
                $stats = $session->getStatistics();
                return [
                    'id' => $session->id,
                    'date' => $session->full_date,
                    'dateStr' => $session->date->format('Y-m-d'),
                    'fullDate' => $session->date->format('d/m/Y'),
                    'dayName' => $this->getVietnameseDayName($session->date),
                    'type' => $session->type,
                    'typeLabel' => $session->type == 1 ? 'Đi học' : 'Đi lễ',
                    'title' => $session->title,
                    'status' => $session->status,
                    'statusLabel' => $this->getStatusLabel($session->status),
                    'statusClass' => $this->getStatusClass($session->status),
                    'locked' => $session->status == AttendanceSession::STATUS_CLOSED,
                    'start_time' => $session->start_time?->format('H:i'),
                    'end_time' => $session->end_time?->format('H:i'),
                    'stats' => $stats,
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
            $newNamHoc = is_numeric($filters['namHoc'])
                ? (int) $filters['namHoc']
                : null;

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
            $newClassId = is_numeric($filters['lop'])
                ? (int) $filters['lop']
                : null;

            if ($newClassId !== $this->selectedClassId) {
                $this->selectedClassId = $newClassId;

                if ($this->selectedClassId) {
                    $this->loadClassInfo();
                    $this->loadSessions();
                } else {
                    $this->reset(['sessions', 'currentNamHoc']);
                }
            }
        }

        if ($namHocChanged) {
            $this->selectedClassId = null;
            $this->reset(['sessions', 'currentNamHoc']);
        }
    }

    // ==================== CRUD ACTIONS ====================

    public function create(): void
    {

        $this->requireManager();

        if (!$this->selectedClassId) {
            session()->flash('warning', 'Vui lòng chọn lớp trước');
            return;
        }

        if (!$this->currentNamHoc) {
            session()->flash('warning', 'Không tìm thấy thông tin năm học');
            return;
        }

        $this->resetForm();

        // Set default dates based on current semester
        $today = Carbon::today();
        $currentSemester = $this->getCurrentSemester();

        if ($currentSemester === 1 && $this->currentNamHoc->start_date_one) {
            $this->startDate = max($today, $this->currentNamHoc->start_date_one)->format('Y-m-d');
            $this->endDate = $this->currentNamHoc->end_date_one?->format('Y-m-d');
        } elseif ($currentSemester === 2 && $this->currentNamHoc->start_date_two) {
            $this->startDate = max($today, $this->currentNamHoc->start_date_two)->format('Y-m-d');
            $this->endDate = $this->currentNamHoc->end_date_two?->format('Y-m-d');
        } else {
            // Fallback to today
            $this->startDate = $today->format('Y-m-d');
        }

        $this->showForm = true;
    }

    public function save(): void
    {
        $this->requireManager();

        // Custom validation based on mode
        if ($this->createMode === 'weekly' && empty($this->weekDays)) {
            session()->flash('error', 'Vui lòng chọn ít nhất 1 ngày trong tuần');
            return;
        }

        if ($this->createMode === 'custom' && empty($this->selectedDates)) {
            session()->flash('error', 'Vui lòng chọn ít nhất 1 ngày');
            return;
        }

        $this->validate($this->rules, $this->messages);

        try {
            DB::beginTransaction();

            $dates = $this->generateDates();

            if (empty($dates)) {
                session()->flash('warning', 'Không có ngày nào được tạo. Vui lòng kiểm tra lại.');
                DB::rollBack();
                return;
            }

            // Check if dates are within năm học range
            $validDates = $this->filterValidDates($dates);

            if (empty($validDates)) {
                session()->flash('warning', 'Tất cả các ngày đều nằm ngoài khoảng thời gian năm học.');
                DB::rollBack();
                return;
            }

            $created = 0;
            $skipped = 0;

            foreach ($validDates as $date) {
                // Check if session already exists
                $exists = AttendanceSession::where('class_id', $this->selectedClassId)
                    ->where('type', $this->type)
                    ->whereDate('date', $date)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                AttendanceSession::create([
                    'class_id' => $this->selectedClassId,
                    'date' => $date,
                    'type' => $this->type,
                    // 'title' => $this->title ?: null,
                    'status' => AttendanceSession::STATUS_OPENING,
                    'start_time' => $this->startTime ? Carbon::parse($this->startTime) : null,
                    'end_time' => $this->endTime ? Carbon::parse($this->endTime) : null,
                ]);

                $created++;
            }

            DB::commit();

            $message = "Đã tạo {$created} phiên điểm danh";
            if ($skipped > 0) {
                $message .= " ({$skipped} phiên đã tồn tại)";
            }

            session()->flash('message', $message);

            $this->resetForm();
            $this->loadSessions();

            $this->emit('sessionCreated');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, 'Error creating sessions', [
                'class_id' => $this->selectedClassId,
                'mode' => $this->createMode,
            ]);

            session()->flash('error', 'Có lỗi khi tạo phiên điểm danh. Vui lòng thử lại.');
        }
    }

    public function toggleStatus(int $id): void
    {
        $this->requireManager();

        try {
            $session = AttendanceSession::where('parish_id', $this->parishId)
                ->where('class_id', $this->selectedClassId)
                ->findOrFail($id);

            $newStatus = $session->status == AttendanceSession::STATUS_OPENING
                ? AttendanceSession::STATUS_CLOSED
                : AttendanceSession::STATUS_OPENING;

            $session->update(['status' => $newStatus]);

            $message = $newStatus == AttendanceSession::STATUS_CLOSED
                ? 'Đã khóa phiên điểm danh'
                : 'Đã mở lại phiên điểm danh';

            session()->flash('message', $message);

            $this->loadSessions();
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling session status', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi thay đổi trạng thái');
        }
    }

    public function delete(int $id): void
    {
        $this->requireManager();

        try {
            DB::beginTransaction();

            $session = AttendanceSession::where('class_id', $this->selectedClassId)
                ->findOrFail($id);

            // Check if has attendance records
            $hasRecords = $session->records()->whereNotNull('status')->exists();

            if ($hasRecords) {
                session()->flash('error', 'Không thể xóa phiên đã có dữ liệu điểm danh');
                DB::rollBack();
                return;
            }

            $session->delete();

            DB::commit();

            session()->flash('message', 'Đã xóa phiên điểm danh');

            $this->loadSessions();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting session', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi xóa phiên điểm danh');
        }
    }

    // ==================== HELPER METHODS ====================

    protected function generateDates(): array
    {
        $dates = [];

        switch ($this->createMode) {
            case 'single':
                $dates[] = $this->startDate;
                break;

            case 'weekly':
                $start = Carbon::parse($this->startDate);
                $end = $this->endDate ? Carbon::parse($this->endDate) : $start->copy()->addMonths(3);

                while ($start <= $end) {
                    if (in_array($start->dayOfWeek, $this->weekDays)) {
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

        $currentSemester = $this->getCurrentSemester();

        $minDate = null;
        $maxDate = null;

        if ($currentSemester === 1) {
            $minDate = $this->currentNamHoc->start_date_one;
            $maxDate = $this->currentNamHoc->end_date_one;
        } elseif ($currentSemester === 2) {
            $minDate = $this->currentNamHoc->start_date_two;
            $maxDate = $this->currentNamHoc->end_date_two;
        }

        if (!$minDate || !$maxDate) {
            return [];
        }

        return array_filter($dates, function ($date) use ($minDate, $maxDate) {
            $carbonDate = Carbon::parse($date);
            return $carbonDate->between($minDate, $maxDate);
        });
    }

    protected function getCurrentSemester(): ?int
    {
        if (!$this->currentNamHoc) {
            return null;
        }

        $today = Carbon::today();

        if ($this->currentNamHoc->start_date_one && $this->currentNamHoc->end_date_one) {
            if ($today->between($this->currentNamHoc->start_date_one, $this->currentNamHoc->end_date_one)) {
                return 1;
            }
        }

        if ($this->currentNamHoc->start_date_two && $this->currentNamHoc->end_date_two) {
            if ($today->between($this->currentNamHoc->start_date_two, $this->currentNamHoc->end_date_two)) {
                return 2;
            }
        }

        return null;
    }

    protected function getVietnameseDayName($date): string
    {
        $days = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
        return $days[$date->dayOfWeek];
    }

    protected function getStatusLabel(int $status): string
    {
        return match ($status) {
            AttendanceSession::STATUS_OPENING => 'Đang mở',
            AttendanceSession::STATUS_CLOSED => 'Đã khóa',
            AttendanceSession::STATUS_CANCELLED => 'Đã hủy',
            default => 'Không xác định',
        };
    }

    protected function getStatusClass(int $status): string
    {
        return match ($status) {
            AttendanceSession::STATUS_OPENING => 'bg-green-100 text-green-700',
            AttendanceSession::STATUS_CLOSED => 'bg-slate-200 text-slate-600',
            AttendanceSession::STATUS_CANCELLED => 'bg-red-100 text-red-700',
            default => 'bg-slate-100 text-slate-500',
        };
    }

    public function closeModal()
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetValidation();
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

        $this->type = 1;
        $this->createMode = 'single';
        $this->showForm = false;
        $this->resetValidation();
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
        return view('livewire.attendance.session-manager', [
            'parishId' => $this->parishId,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
