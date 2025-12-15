<?php

namespace App\Http\Livewire;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Lop;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Livewire\Component;

class AttendanceManager extends Component
{
    // Inject Service
    protected $attendanceService;

    // Properties
    // public $parish_id;
    public $selectedClassId;
    public $selectedNamHoc = '';
    public $selectedKhoi = '';
    public $selectedLop = '';
    public $selectedKy = '';
    public $attendanceType = 1; // 1: học, 2: lễ


    public $classes = [];
    public $students = [];
    public $sessions = [];
    public $attendanceRecords = [];

    // View mode
    public $viewMode = 'desktop'; // desktop hoặc mobile
    public $selectedDate = null; // Cho mobile view

    // Search/Filter (cho 30+ học sinh)
    public $searchTerm = '';
    public $filterStatus = 'all'; // all, present, absent

    // protected $listeners = ['refreshAttendance' => '$refresh'];
    protected $listeners = [
        'filtersChanged' => 'handleFiltersChanged'
    ];

    public function handleFiltersChanged($filters)
    {
        $this->selectedNamHoc = $filters['namHoc'] ?? '';
        $this->selectedKhoi = $filters['khoi'] ?? '';
        $this->selectedLop = $filters['lop'] ?? '';
        $this->selectedKy = $filters['ky'] ?? '';

        // Reload data based on filters
        if ($this->selectedLop) {
            $this->loadStudents();
            $this->loadSessions();
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


        $this->classes = Lop::where('status', 1)
            // ->where('schoolyear', $this->namHocId)
            ->orderBy('name')
            ->get();

        $this->selectedClassId = $classId ?? $this->classes->first()?->id;

        if ($this->selectedClassId) {
            $this->loadStudents();
            $this->loadSessions();
        }

        // Detect mobile
        $this->detectViewMode();
    }

    /**
     * Detect view mode based on screen size
     */
    public function detectViewMode()
    {
        // Bạn có thể dùng JavaScript để detect, hoặc mặc định desktop
        // Ở đây mình để mặc định, sau này có thể thêm JS
        $this->viewMode = 'desktop';
    }

    /**
     * Load students của lớp được chọn
     */
    public function loadStudents()
    {
        $lop = Lop::with(['students' => function ($q) {
            // Search
            if (!empty($this->searchTerm)) {
                $search = '%' . $this->searchTerm . '%';
                $q->where(function ($qq) use ($search) {
                    $qq->where('saint_name', 'like', $search)
                        ->orWhere('name', 'like', $search)
                        ->orWhere('last_name', 'like', $search);
                });
            }

            // Chỉ lấy học sinh đang học
            $q->wherePivot('status', 1);

            // Sắp xếp
            $q->orderBy('name');
        }])
            ->find($this->selectedClassId);

        $this->students = $lop ? $lop->students : collect();
    }

    public function loadSessions()
    {
        $sessions = AttendanceSession::where('class_id', $this->selectedClassId)
            ->when($this->attendanceType, function ($q) {
                return $q->where('type', $this->attendanceType);
            })
            ->orderBy('date')
            ->get();

        $this->sessions = $sessions->map(function ($s) {
            $date = Carbon::parse($s->date);

            return [
                'id'        => $s->id,
                'date'      => $date,
                'dateStr'   => $date->format('Y-m-d'),
                'fullDate'  => $date->format('d/m'),
                'dayName'   => $this->getVietnameseDayName($date),
                'type'      => $s->type,
                'status'    => $s->status,
                'locked'    => $s->status == AttendanceSession::STATUS_CLOSED,
            ];
        })->toArray();

        // Load attendance records
        $this->loadAttendanceRecords();

        // Set selected date (cho mobile)
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
     * Change class
     */
    public function changeClass($classId)
    {
        $this->selectedClassId = $classId;
        $this->searchTerm = ''; // Reset search
        $this->filterStatus = 'all';
        $this->loadStudents();
        $this->loadSessions();
    }

    /**
     * Change attendance type
     */
    public function changeType($type)
    {
        $this->attendanceType = $type;
        $this->loadSessions();
    }

    /**
     * Update search term
     */
    public function updatedSearchTerm()
    {
        $this->loadStudents();
    }

    /**
     * Set attendance for a student
     * Sử dụng Service để xử lý logic
     */
    public function setAttendance($studentId, $sessionId, $status)
    {
        try {
            // Tìm session theo ID (không theo ngày nữa!)
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

            // Gọi service
            $result = $this->attendanceService->setAttendanceRecord(
                $this->selectedClassId,
                $studentId,
                $sessionId,
                $this->attendanceType,
                $status
            );

            if ($result['success']) {
                $this->loadAttendanceRecords();

                $this->dispatchBrowserEvent('show-alert', [
                    'type' => 'success',
                    'message' => 'Cập nhật điểm danh thành công!'
                ]);
            } else {
                $this->dispatchBrowserEvent('show-alert', [
                    'type' => 'error',
                    'message' => $result['message'] ?? 'Có lỗi xảy ra'
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('show-alert', [
                'type' => 'error',
                'message' => 'Lỗi: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mark all present for a date
     * Sử dụng Service
     */
    public function markAllPresent($sessionId)
    {
        try {
            // Lấy session theo ID
            $session = collect($this->sessions)->firstWhere('id', $sessionId);

            if (!$session || $session['locked']) {
                $this->dispatchBrowserEvent('show-alert', [
                    'type' => 'error',
                    'message' => 'Không thể điểm danh cho buổi này!'
                ]);
                return;
            }

            // Chuẩn bị dữ liệu
            $attendanceData = $this->students->map(fn($s) => [
                'student_id' => $s->id,
                'status' => AttendanceRecord::STATUS_PRESENT,
            ])->toArray();

            // Gọi service
            $result = $this->attendanceService->bulkImportBySessionId(
                $sessionId,
                $attendanceData
            );

            if ($result['success']) {
                $this->loadAttendanceRecords();

                $this->dispatchBrowserEvent('show-alert', [
                    'type' => 'success',
                    'message' => 'Đã điểm danh tất cả có mặt!'
                ]);
            } else {
                $this->dispatchBrowserEvent('show-alert', [
                    'type' => 'error',
                    'message' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('show-alert', [
                'type' => 'error',
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get attendance status for a student on a date
     */
    public function getAttendanceStatus($studentId, $dateStr)
    {
        $key = $studentId . '_' . $dateStr;

        // Nếu có bản ghi điểm danh → trả về status
        if (isset($this->attendanceRecords[$key])) {
            return $this->attendanceRecords[$key]['status'] ?? null;
        }

        return null;
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
        $sessionId = $this->selectedSession;

        if (!$sessionId) {
            $this->dispatchBrowserEvent('show-alert', [
                'type' => 'error',
                'message' => 'Chưa chọn buổi điểm danh!'
            ]);
            return;
        }

        // Lấy username
        $username = auth()->user()->username ?? 'system';

        // Gọi service với mảng studentId => status
        $this->attendanceService->saveAttendance(
            $sessionId,
            $this->statuses,
            $username
        );

        $this->dispatchBrowserEvent('show-alert', [
            'type' => 'success',
            'message' => 'Đã lưu điểm danh thành công!'
        ]);

        $this->loadAttendanceRecords(); // reload
    }

    /**
     * Render component
     */
    public function render()
    {

        // dd('MOUNT RUNNING', [
        //     'sessions' => $this->sessions ?? null,
        // ]);

        return view('livewire.attendance-manager')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
