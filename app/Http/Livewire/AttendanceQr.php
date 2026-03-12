<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use App\Models\StudentNew;
use App\Services\AttendanceService;

class AttendanceQr extends BaseComponent
{
    protected AttendanceService $attendanceService;

    // ==================== PROPS từ URL ====================

    /** @var int|null */
    public $classId = null;

    /** @var int|null */
    public $sessionId = null;

    /** @var int 1=học, 2=lễ */
    public $type = 1;

    // ==================== STATE ====================

    /** @var bool Camera đang bật */
    public $scanning = false;

    /** @var array|null Kết quả scan gần nhất */
    public $lastResult = null;

    /** @var string 'success'|'warning'|'error'|null */
    public $lastResultType = null;

    /** @var array Danh sách đã điểm danh trong session này */
    public $scannedLog = [];

    // ==================== DATA ====================

    /** @var array|null Thông tin session hiện tại */
    public $session = null;

    /** @var array|null Thông tin lớp */
    public $classInfo = null;

    /** @var int Tổng học sinh */
    public $totalStudents = 0;

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return [
            'classId'   => ['except' => null],
            'sessionId' => ['except' => null],
            'type'      => ['except' => 1],
        ];
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'qrScanned' => 'handleQrScanned',
    ];

    // ==================== LIFECYCLE ====================

    public function boot(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount()
    {
        parent::mount();
        $this->requireParishId();
        $this->loadSessionInfo();
    }

    protected function loadInitialData(): void
    {
        // Không dùng
    }

    // ==================== DATA LOADING ====================

    protected function loadSessionInfo(): void
    {
        if (!$this->sessionId || !$this->classId) {
            return;
        }

        try {
            $session = AttendanceSession::findOrFail($this->sessionId);
            $class   = CatechismClass::with('gradLevel')
                ->findOrFail($this->classId);

            $this->session = [
                'id'     => $session->id,
                'date'   => $session->date->format('d/m/Y'),
                'locked' => $session->status === AttendanceSession::STATUS_CLOSED,
                'type'   => $session->type,
            ];

            $this->classInfo = [
                'id'   => $class->id,
                'name' => $class->name,
            ];

            $this->totalStudents = $class->students()
                ->wherePivot('status', 1)
                ->count();

            // Load log những ai đã được điểm danh trong session này
            $this->scannedLog = AttendanceRecord::where('session_id', $this->sessionId)
                ->with('student')
                ->get()
                ->map(fn($r) => [
                    'student_id'   => $r->student_id,
                    'student_name' => $r->student?->full_name ?? '—',
                    'saint_name'   => $r->student?->saint_name ?? '',
                    'status'       => $r->status,
                    'time'         => $r->created_at?->format('H:i'),
                ])
                ->toArray();
        } catch (\Exception $e) {
            $this->logError($e, 'QR: Error loading session info');
            session()->flash('error', 'Không tìm thấy buổi học này');
        }
    }

    // ==================== QR HANDLER ====================

    /**
     * Được gọi từ JS sau khi quét được QR
     * $token = qr_token của học sinh
     */
    public function handleQrScanned(string $token): void
    {
        if (!$this->sessionId) {
            $this->lastResult     = ['message' => 'Chưa chọn buổi học'];
            $this->lastResultType = 'error';
            return;
        }

        if ($this->session['locked'] ?? false) {
            $this->lastResult     = ['message' => 'Buổi học đã khóa, không thể điểm danh'];
            $this->lastResultType = 'error';
            return;
        }

        try {
            // Tìm học sinh theo qr_token
            $student = StudentNew::where('qr_token', $token)
                ->first();

            if (!$student) {
                $this->lastResult     = ['message' => 'Mã QR không hợp lệ'];
                $this->lastResultType = 'error';
                return;
            }

            // Kiểm tra học sinh có trong lớp không
            $inClass = $student->classes()
                ->where('class_id', $this->classId)
                ->wherePivot('status', 1)
                ->exists();

            if (!$inClass) {
                $this->lastResult = [
                    'message'      => 'Học sinh không thuộc lớp này',
                    'student_name' => $student->full_name,
                    'saint_name'   => $student->saint_name,
                ];
                $this->lastResultType = 'warning';
                return;
            }

            // Kiểm tra đã điểm danh chưa
            $existing = AttendanceRecord::where('session_id', $this->sessionId)
                ->where('student_id', $student->id)
                ->first();

            if ($existing) {
                $this->lastResult = [
                    'message'      => 'Đã điểm danh trước đó',
                    'student_name' => $student->full_name,
                    'saint_name'   => $student->saint_name,
                    'time'         => $existing->created_at?->format('H:i'),
                ];
                $this->lastResultType = 'warning';
                return;
            }

            // Lưu điểm danh có mặt
            AttendanceRecord::create([
                'session_id' => $this->sessionId,
                'student_id' => $student->id,
                'status'     => AttendanceRecord::STATUS_PRESENT,
                'note'       => '',
            ]);

            // Thêm vào log
            array_unshift($this->scannedLog, [
                'student_id'   => $student->id,
                'student_name' => $student->full_name,
                'saint_name'   => $student->saint_name,
                'status'       => AttendanceRecord::STATUS_PRESENT,
                'time'         => now()->format('H:i'),
            ]);

            $this->lastResult = [
                'message'      => 'Điểm danh thành công!',
                'student_name' => $student->full_name,
                'saint_name'   => $student->saint_name,
            ];
            $this->lastResultType = 'success';
        } catch (\Exception $e) {
            $this->logError($e, 'QR scan error', ['token' => substr($token, 0, 8)]);
            $this->lastResult     = ['message' => 'Có lỗi xảy ra, vui lòng thử lại'];
            $this->lastResultType = 'error';
        }
    }

    public function clearResult(): void
    {
        $this->lastResult     = null;
        $this->lastResultType = null;
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.attendance-qr')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
