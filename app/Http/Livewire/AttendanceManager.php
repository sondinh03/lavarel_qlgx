<?php

namespace App\Http\Livewire;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Decen;
use App\Models\Lop;
use App\Models\SetAdmin;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AttendanceManager extends Component
{
    // Inject Service
    protected $attendanceService;

    // Properties
    public $parish_id;
    public $selectedClassId;
    public $selectedNamHoc = '';
    public $selectedKhoi = '';
    public $selectedLop = '';
    public $selectedKy = '';
    public $attendanceType = 1; // 1: học, 2: lễ
    public $isAdmin = false;

    public $classes = [];
    public $students = [];
    public $sessions = [];
    public $attendanceRecords = [];

    // View mode
    public $viewMode = 'desktop'; // desktop hoặc mobile
    public $selectedDate = null; // Cho mobile view

    public $searchTerm = '';
    public $filterStatus = 'all'; // all, present, absent

    public $draftAttendance = [];

    protected $queryString = [
        'attendanceType' => ['as' => 'type', 'except' => 1],
        'selectedDate'   => ['as' => 'date', 'except' => null],
    ];

    // protected $listeners = ['refreshAttendance' => '$refresh'];
    protected $listeners = [
        'filtersChanged' => 'handleFiltersChanged'
    ];

    public function handleFiltersChanged($filters)
    {
        $this->selectedClassId = $filters['lop'] ?? null;

        // Reload data based on filters
        if ($this->selectedClassId) {
            $this->loadStudents();
            $this->loadSessions();
        } else {
            $this->students = [];
            $this->sessions = [];
            $this->attendanceRecords = [];
        }

        if (!empty($filters['lop'])) {
            $this->changeClass($filters['lop']);
        }
    }

    public function getSelectedClassNameProperty(): string
    {
        if (!$this->selectedClassId) {
            return 'Chọn lớp';
        }

        return Lop::where('id', $this->selectedClassId)
            ->value('name') ?? 'Chọn lớp';
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
        $this->initializeUser();

        $this->selectedClassId = $classId;

        if ($classId) {
            $lop = Lop::with('blockRelation')->find($classId);

            if ($lop) {
                $this->selectedNamHoc = $lop->schoolyear;
                $this->selectedKhoi   = $lop->block;
            }

            $this->loadStudents();
            $this->loadSessions();
        }

        // Detect mobile
        $this->detectViewMode();
    }

    private function initializeUser(): void
    {
        $user = backpack_user();

        if (!$user) {
            if (!$user) {
                abort(403, 'Vui lòng đăng nhập');
            }
        }

        $userId = $user->id;

        $setadmin = SetAdmin::where('use', $userId)
            ->where('status', 1)
            ->first();

        if ($setadmin) {
            // Admin - lấy giáo xứ từ request
            $this->isAdmin = true;
            $this->parish_id = request()->get('giaoxu');
            return;
        }

        $decen = Decen::where('use', $userId)
            ->where('status', 1)
            ->where('student', 1)
            ->first();

        if ($decen) {
            $this->parish_id = $decen->pid;
            $this->isAdmin = false;
        }
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
        try {
            // Validate trước khi query
            if (!$this->selectedClassId) {
                $this->reset(['sessions', 'selectedDate', 'attendanceRecords']);
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

            if (empty($this->sessions)) {
                session()->flash('info', 'Chưa có buổi điểm danh nào');
            }
        } catch (\Exception $e) {
            Log::error('Error loading sessions', [
                'class_id' => $this->selectedClassId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->reset(['sessions', 'selectedDate', 'attendanceRecords']);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Không thể tải danh sách buổi học. Vui lòng thử lại.'
            ]);
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
        // $this->selectedClassId = $classId;
        // $this->searchTerm = ''; // Reset search
        // $this->filterStatus = 'all';
        // $this->loadStudents();
        // $this->loadSessions();

        return redirect()->route('attendance', [
            'classId' => $classId,
            'type'    => $this->attendanceType,
            'date'    => $this->selectedDate,
        ]);
    }

    /**
     * Change attendance type
     */
    public function changeType($type)
    {
        if (!empty($this->draftAttendance)) {
            $this->dispatchBrowserEvent('show-alert', [
                'type' => 'warning',
                'message' => 'Bạn có dữ liệu chưa lưu'
            ]);
            return;
        }

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
        $session = collect($this->sessions)->firstWhere('id', $sessionId);

        if (!$session || $session['locked']) {
            return;
        }

        $key = $studentId . '_' . $sessionId;

        if ($status == null) {
            unset($this->draftAttendance[$key]);
        } else {
            $this->draftAttendance[$key] = [
                'student_id'     => $studentId,
                'session_id'     => $sessionId,
                'status'         => $status,
                'attendanceType' => $this->attendanceType,
            ];
        }
    }

    /**
     * Mark all present for a date
     * Sử dụng Service
     */
    public function markAllPresent($sessionId)
    {
        // Lấy session theo ID
        $session = collect($this->sessions)->firstWhere('id', $sessionId);

        if (!$session || $session['locked']) {
            $this->dispatchBrowserEvent('show-alert', [
                'type' => 'error',
                'message' => 'Không thể điểm danh cho buổi này!'
            ]);
            return;
        }

        foreach ($this->students as $student) {
            $key = $student->id . '_' . $sessionId;
            $this->draftAttendance[$key] = [
                'student_id'     => $student->id,
                'session_id'     => $sessionId,
                'status'         => AttendanceRecord::STATUS_PRESENT,
                'attendanceType' => $this->attendanceType,
            ];
        }

        $this->dispatchBrowserEvent('show-alert', [
            'type' => 'success',
            'message' => 'Đã đánh dấu tất cả có mặt (chỉ lưu tạm)'
        ]);
    }

    /**
     * Get attendance status for a student on a date
     */
    public function getAttendanceStatus($studentId, $dateStr)
    {
        // $key = $studentId . '_' . $dateStr;

        // // Nếu có bản ghi điểm danh → trả về status
        // if (isset($this->attendanceRecords[$key])) {
        //     return $this->attendanceRecords[$key]['status'] ?? null;
        // }

        // return null;

        // $session = collect($this->sessions)
        //     ->first(fn($s) => $s['dateStr'] == $dateStr);

        $session = collect($this->sessions)
            ->firstWhere('dateStr', $dateStr);

        if (!$session) {
            return null;
        }

        $draftKey = $studentId . '_' . $session['id'];

        if (isset($this->draftAttendance[$draftKey])) {
            return $this->draftAttendance[$draftKey]['status'];
        }

        $recordKey = $studentId . '_' . $dateStr;
        return $this->attendanceRecords[$recordKey]['status'] ?? null;
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

    public function saveAttendance()
    {
        $drafts = collect($this->draftAttendance)
            ->where('attendanceType', $this->attendanceType);

        if ($drafts->isEmpty()) {
            $this->dispatchBrowserEvent('show-alert', [
                'type' => 'warning',
                'message' => 'Không có dữ liệu để lưu'
            ]);
            return;
        }

        $result = $this->attendanceService->saveBulkAttendance($drafts->values()->toArray());

        if ($result['success']) {
            // Xóa draft đã lưu
            foreach ($drafts as $key => $item) {
                unset($this->draftAttendance[$key]);
            }

            $this->loadAttendanceRecords();

            $this->dispatchBrowserEvent('show-alert', [
                'type' => 'success',
                'message' => 'Đã lưu điểm danh'
            ]);
        } else {
            $this->dispatchBrowserEvent('show-alert', [
                'type' => 'error',
                'message' => $result['message']
            ]);
        }
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
        return view('livewire.attendance-manager')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
