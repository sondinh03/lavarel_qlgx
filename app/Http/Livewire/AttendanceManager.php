<?php

namespace App\Http\Livewire;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Lop;
use App\Services\AttendanceService;
use App\Traits\FilterTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AttendanceManager extends Component
{
    use FilterTrait;

    // Inject Service
    protected $attendanceService;

    // Properties for Filter Selector
    public $parish_id;
    public $selectedNamHoc;
    public $selectedKhoi;
    public $selectedLop;
    public $selectedKy;

    // Original properties
    public $selectedClassId;
    public $attendanceType = 1; // 1: học, 2: lễ

    public $namHocs = [];
    public $classes = [];
    public $students = [];
    public $sessions = [];
    // public $attendanceRecords = [];

    // Thay đổi: Lưu trạng thái tạm thời trong session
    public $pendingAttendance = []; // ['session_id' => ['student_id' => status]]
    public $hasUnsavedChanges = false;

    // View mode
    public $viewMode = 'desktop';
    public $selectedDate = null;

    // Search/Filter
    public $searchTerm = '';
    public $filterStatus = 'all';

    protected $listeners = [
        'filtersChanged' => 'handleFiltersChanged'
    ];

    public function handleFiltersChanged($filters)
    {
        $this->selectedNamHoc = $filters['namHoc'] ?? null;
        $this->selectedKhoi = $filters['khoi'] ?? null;
        $this->selectedLop = $filters['lop'] ?? null;
        $this->selectedKy = $filters['ky'] ?? null;

        if ($this->selectedLop) {
            $this->selectedClassId = $this->selectedLop;
            $this->loadClassData();
        } else {
            $this->resetClassData();
        }
    }

    /**
     * Mount component
     */
    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount($classId = null)
    {
        $this->parish_id = auth()->user()->parish_id ?? config('settings.default_parish_id', 1);

        Log::info('Attendance mount: parish_id', [
            'parish_id' => $this->parish_id,
            'user_id' => auth()->id(),
        ]);

        $data = $this->getNamHocs($this->parish_id);
        $this->namHocs = $data['namHocs'];

        $this->classes = Lop::where('status', 1)
            ->orderBy('name')
            ->get();

        $this->selectedClassId = $classId ?? $this->classes->first()?->id;
        $this->selectedLop = $this->selectedClassId;

        if ($this->selectedClassId) {
            $lop = Lop::with(['schoolYear', 'blockRelation'])->find($this->selectedClassId);
            if ($lop) {
                $this->selectedNamHoc = $lop->schoolyear;
                $this->selectedKhoi = $lop->block;
            }

            $this->loadClassData();
        }

        $this->detectViewMode();
    }

    public function detectViewMode()
    {
        $this->viewMode = 'desktop';
    }

    /**
     * Load tất cả dữ liệu của lớp
     */
    private function loadClassData()
    {
        $this->loadStudents();
        $this->loadSessions();
        $this->initializePendingAttendance();
    }

    /**
     * Reset dữ liệu khi không có lớp
     */
    private function resetClassData()
    {
        $this->students = collect();
        $this->sessions = [];
        $this->pendingAttendance = [];
        $this->hasUnsavedChanges = false;
    }

    /**
     * Load students
     */
    public function loadStudents()
    {
        if (!$this->selectedClassId) {
            $this->students = collect();
            return;
        }

        $lop = Lop::with(['students' => function ($q) {
            if (!empty($this->searchTerm)) {
                $search = '%' . $this->searchTerm . '%';
                $q->where(function ($qq) use ($search) {
                    $qq->where('saint_name', 'like', $search)
                        ->orWhere('name', 'like', $search)
                        ->orWhere('last_name', 'like', $search);
                });
            }

            $q->wherePivot('status', 1);
            $q->orderBy('name');
        }])->find($this->selectedClassId);

        $this->students = $lop ? $lop->students : collect();
    }

    /**
     * Load sessions và attendance records
     */
    public function loadSessions()
    {
        if (!$this->selectedClassId) {
            $this->sessions = [];
            return;
        }

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

        if (!$this->selectedDate && !empty($this->sessions)) {
            $unlocked = collect($this->sessions)->first(fn($s) => !$s['locked']);
            $this->selectedDate = $unlocked
                ? $unlocked['dateStr']
                : $this->sessions[0]['dateStr'];
        }
    }


    /**
     * Get Vietnamese day name
     */
    private function getVietnameseDayName($date)
    {
        $days = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];
        return $days[$date->dayOfWeek];
    }

    public function loadAttendanceRecords()
    {
        $sessionIds = collect($this->sessions)
            ->pluck('id')
            ->toArray();

        if (empty($sessionIds)) {
            $this->attendanceRecords = [];
            return;
        }

        $records = AttendanceRecord::whereIn('session_id', $sessionIds)
            ->with('session')
            ->get()
            ->groupBy(function ($r) {
                return $r->student_id . '_' . Carbon::parse($r->session->date)->format('Y-m-d');
            });

        $this->attendanceRecords = $records->map(fn($g) => $g->first())->toArray();
    }

    /**
     * Khởi tạo pendingAttendance từ database
     */
    private function initializePendingAttendance()
    {
        $sessionIds = collect($this->sessions)->pluck('id')->toArray();

        if (empty($sessionIds)) {
            $this->pendingAttendance = [];
            return;
        }

        $records = AttendanceRecord::whereIn('session_id', $sessionIds)
            ->get();

        $this->pendingAttendance = [];
        foreach ($records as $record) {
            if (!isset($this->pendingAttendance[$record->session_id])) {
                $this->pendingAttendance[$record->session_id] = [];
            }
            $this->pendingAttendance[$record->session_id][$record->student_id] = $record->status;
        }

        $this->hasUnsavedChanges = false;
    }

    /**
     * Change class
     */
    public function changeClass($classId)
    {
        if ($this->hasUnsavedChanges) {
            $this->dispatchBrowserEvent('confirm-change-class', [
                'classId' => $classId,
                'message' => 'Bạn có thay đổi chưa lưu. Bạn có chắc chắn muốn đổi lớp?'
            ]);
            return;
        }

        $this->performChangeClass($classId);
    }

    public function performChangeClass($classId)
    {
        $this->selectedClassId = $classId;
        $this->selectedLop = $classId;
        $this->searchTerm = '';
        $this->filterStatus = 'all';

        if ($classId) {
            $lop = Lop::with(['schoolYear', 'blockRelation'])->find($classId);
            if ($lop) {
                $this->selectedNamHoc = $lop->schoolyear;
                $this->selectedKhoi = $lop->block;
            }
        }

        $this->loadClassData();
    }

    public function changeType($type)
    {
        if ($this->hasUnsavedChanges) {
            $this->dispatchBrowserEvent('confirm-change-type', [
                'type' => $type,
                'message' => 'Bạn có thay đổi chưa lưu. Bạn có chắc chắn muốn đổi loại điểm danh?'
            ]);
            return;
        }

        $this->performChangeType($type);
    }

    public function performChangeType($type)
    {
        $this->attendanceType = $type;
        $this->loadSessions();
        $this->initializePendingAttendance();
    }

    public function updatedSearchTerm()
    {
        $this->loadStudents();
    }

    /**
     * Toggle attendance (thay vì lưu ngay)
     */
    public function toggleAttendance($studentId, $sessionId, $status)
    {
        $session = collect($this->sessions)->firstWhere('id', $sessionId);

        if (!$session) {
            $this->dispatchBrowserEvent('show-alert', [
                'type' => 'error',
                'message' => 'Không tìm thấy buổi điểm danh!'
            ]);
            return;
        }

        if ($session['locked']) {
            $this->dispatchBrowserEvent('show-alert', [
                'type' => 'error',
                'message' => 'Buổi điểm danh đã khóa!'
            ]);
            return;
        }

        // Cập nhật pending attendance
        if (!isset($this->pendingAttendance[$sessionId])) {
            $this->pendingAttendance[$sessionId] = [];
        }

        if ($status === null) {
            unset($this->pendingAttendance[$sessionId][$studentId]);
        } else {
            $this->pendingAttendance[$sessionId][$studentId] = $status;
        }

        $this->hasUnsavedChanges = true;
    }

    /**
     * Mark all present for a date
     * Sử dụng Service
     */
    public function markAllPresent($sessionId)
    {
        $session = collect($this->sessions)->firstWhere('id', $sessionId);

        if (!$session || $session['locked']) {
            $this->dispatchBrowserEvent('show-alert', [
                'type' => 'error',
                'message' => 'Không thể điểm danh cho buổi này!'
            ]);
            return;
        }

        if (!isset($this->pendingAttendance[$sessionId])) {
            $this->pendingAttendance[$sessionId] = [];
        }

        foreach ($this->students as $student) {
            $this->pendingAttendance[$sessionId][$student->id] = AttendanceRecord::STATUS_PRESENT;
        }

        $this->hasUnsavedChanges = true;

        $this->dispatchBrowserEvent('show-alert', [
            'type' => 'info',
            'message' => 'Đã đánh dấu tất cả có mặt. Nhấn "Lưu" để cập nhật!'
        ]);
    }
    
    public function saveAttendance()
    {
        if (!$this->hasUnsavedChanges) {
            $this->dispatchBrowserEvent('show-alert', [
                'type' => 'info',
                'message' => 'Không có thay đổi nào để lưu!'
            ]);
            return;
        }

        try {
            // Gọi service để lưu hàng loạt
            $result = $this->attendanceService->saveBulkAttendance($this->pendingAttendance);

            if ($result['success']) {
                $this->hasUnsavedChanges = false;

                $this->dispatchBrowserEvent('show-alert', [
                    'type' => 'success',
                    'message' => 'Lưu điểm danh thành công!'
                ]);

                // Reload để đồng bộ với database
                $this->initializePendingAttendance();
            } else {
                $this->dispatchBrowserEvent('show-alert', [
                    'type' => 'error',
                    'message' => $result['message'] ?? 'Có lỗi xảy ra khi lưu điểm danh!'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Save attendance error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatchBrowserEvent('show-alert', [
                'type' => 'error',
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
    }

    public function cancelChanges()
    {
        $this->initializePendingAttendance();

        $this->dispatchBrowserEvent('show-alert', [
            'type' => 'info',
            'message' => 'Đã hủy các thay đổi!'
        ]);
    }

    /**
     * Get attendance status
     */
    public function getAttendanceStatus($studentId, $sessionId)
    {
        return $this->pendingAttendance[$sessionId][$studentId] ?? null;
    }

    /**
     * Get stats for a session
     */
    public function getSessionStats($sessionId)
    {
        $present = 0;
        $absentPermitted = 0;
        $absentNotPermitted = 0;

        if (isset($this->pendingAttendance[$sessionId])) {
            foreach ($this->pendingAttendance[$sessionId] as $status) {
                if ($status == AttendanceRecord::STATUS_PRESENT) {
                    $present++;
                } elseif ($status == AttendanceRecord::STATUS_ABSENT_EXCUSED) {
                    $absentPermitted++;
                } elseif ($status == AttendanceRecord::STATUS_ABSENT_UNEXCUSED) {
                    $absentNotPermitted++;
                }
            }
        }

        return [
            'present' => $present,
            'absentPermitted' => $absentPermitted,
            'absentNotPermitted' => $absentNotPermitted,
            'total' => $this->students->count(),
        ];
    }

    /**
     * Get stats for a date
     */
    public function getDateStats($dateStr)
    {
        $present = 0;
        $absentPermitted = 0;
        $absentNotPermitted = 0;

        foreach ($this->students as $student) {
            $status = $this->getAttendanceStatus($student->id, $dateStr);

            if ($status == AttendanceRecord::STATUS_PRESENT) {
                $present++;
            } elseif ($status == AttendanceRecord::STATUS_ABSENT_EXCUSED) {
                $absentPermitted++;
            } elseif ($status == AttendanceRecord::STATUS_ABSENT_UNEXCUSED) {
                $absentNotPermitted++;
            }
        }

        return [
            'present' => $present,
            'absentPermitted' => $absentPermitted,
            'absentNotPermitted' => $absentNotPermitted,
        ];
    }

    /**
     * Save and close (optional)
     */
    public function saveAndClose()
    {
        // Just reload or redirect
        $this->dispatchBrowserEvent('show-alert', [
            'type' => 'success',
            'message' => 'Đã lưu điểm danh thành công!'
        ]);

        // Optional: redirect back to attendance list
        // return redirect()->route('attendance');
    }

    /**
     * Render component
     */
    public function render()
    {
        return view('livewire.attendance-manager')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
