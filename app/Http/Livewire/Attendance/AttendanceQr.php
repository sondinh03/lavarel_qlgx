<?php

namespace App\Http\Livewire\Attendance;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\StudentNew;
use App\Models\Teacher;
use App\Models\TeacherAttendanceRecord;
use App\Models\TeacherAttendanceSession;
use App\Services\SchoolYearResolver;
use Illuminate\Support\Facades\Log;

class AttendanceQr extends BaseComponent
{
    // ==================== STATE ====================

    /** @var int 1=học/dạy, 2=lễ, 3=họp (chỉ GLV) */
    public int $type = 1;

    /** @var array|null Kết quả scan gần nhất */
    public $lastResult = null;

    /** @var string 'success'|'warning'|'error'|null */
    public $lastResultType = null;

    /** @var array Danh sách đã điểm danh trong session này (in-memory) */
    public array $scannedLog = [];

    // ==================== LISTENERS ====================

    protected $listeners = [
        'qrScanned' => 'handleQrScanned',
    ];

    // ==================== LIFECYCLE ====================

    public function mount()
    {
        parent::mount();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        // Không có data cần load trước — session được resolve lúc quét
    }

    // ==================== ACTIONS ====================

    public function setType(int $type): void
    {
        $this->type = in_array($type, [1, 2, 3], true) ? $type : 1;
        $this->scannedLog = [];
        $this->clearResult();
    }

    public function clearResult(): void
    {
        $this->lastResult     = null;
        $this->lastResultType = null;
    }

    // ==================== QR HANDLER ====================

    /**
     * Được gọi từ JS sau khi quét được QR.
     * $token = qr_token của học sinh hoặc GLV
     */
    public function handleQrScanned(string $token): void
    {
        if ($this->assignmentBlocked) {
            $this->setResult('error', [
                'message' => 'Bạn chưa được phân công lớp trong năm học này',
            ]);
            $this->dispatchBrowserEvent('qr-done');

            return;
        }

        $token = trim($token);

        Log::info('QR scanned', [
            'token' => substr($token, 0, 8),
            'type'  => $this->type,
        ]);

        try {
            if (! $this->isUuidToken($token)) {
                $this->setResult('error', ['message' => 'Mã QR không hợp lệ']);

                return;
            }

            $teacher = Teacher::query()
                ->where('qr_token', $token)
                ->when($this->parishId, fn ($q) => $q->where('parish_id', $this->parishId))
                ->with('saint:id,name')
                ->first();

            if ($teacher) {
                if (! auth()->user()?->canMarkTeacherAttendance($this->parishId ? (int) $this->parishId : null)) {
                    $this->setResult('error', [
                        'message' => 'Bạn không có quyền điểm danh giáo lý viên',
                    ]);

                    return;
                }

                $this->handleTeacherQr($teacher);

                return;
            }

            $this->handleStudentQr($token);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] === 1062) {
                $this->setResult('warning', ['message' => 'Đã điểm danh trước đó']);

                return;
            }
            $this->logError($e, 'QR scan error', ['token' => substr($token, 0, 8)]);
            $this->setResult('error', ['message' => 'Có lỗi xảy ra, vui lòng thử lại']);
        } catch (\Exception $e) {
            $this->logError($e, 'QR scan error', ['token' => substr($token, 0, 8)]);
            $this->setResult('error', ['message' => 'Có lỗi xảy ra, vui lòng thử lại']);
        } finally {
            $this->dispatchBrowserEvent('qr-done');
        }
    }

    protected function handleStudentQr(string $token): void
    {
        if ((int) $this->type === TeacherAttendanceSession::TYPE_MEETING) {
            $this->setResult('error', [
                'message' => 'Loại Họp chỉ dùng cho giáo lý viên',
            ]);

            return;
        }

        $student = StudentNew::where('qr_token', $token)
            ->when($this->parishId, fn ($q) => $q->where('parish_id', $this->parishId))
            ->first();

        if (! $student) {
            $this->setResult('error', ['message' => 'Không tìm thấy học sinh / GLV']);

            return;
        }

        if ($this->parishId && (int) $student->parish_id !== (int) $this->parishId) {
            $this->setResult('error', ['message' => 'Học sinh không thuộc giáo xứ của bạn']);

            return;
        }

        $class = $student->classes()
            ->where('classes.parish_id', $this->parishId)
            ->wherePivot('status', 1)
            ->first();

        if (! $class) {
            $this->setResult('error', [
                'message'      => 'Học sinh chưa được xếp lớp',
                'student_name' => $student->full_name,
                'saint_name'   => $student->saint_name,
            ]);

            return;
        }

        if ($this->parishId && (int) $class->parish_id !== (int) $this->parishId) {
            $this->setResult('error', ['message' => 'Lớp không thuộc giáo xứ của bạn']);

            return;
        }

        $session = AttendanceSession::where('class_id', $class->id)
            ->where('type', $this->type)
            ->whereDate('date', today())
            ->where('status', '!=', AttendanceSession::STATUS_CLOSED)
            ->first();

        if (! $session) {
            $typeLabel = $this->type === 1 ? 'học' : 'lễ';
            $this->setResult('error', [
                'message'      => "Lớp {$class->name} hôm nay không có buổi {$typeLabel}",
                'saint_name'   => $student->saint_name,
                'student_name' => $student->full_name,
            ]);

            return;
        }

        $record = AttendanceRecord::firstOrCreate(
            [
                'session_id' => $session->id,
                'student_id' => $student->id,
            ],
            [
                'status' => AttendanceRecord::STATUS_PRESENT,
                'note'   => '',
            ]
        );

        if (! $record->wasRecentlyCreated) {
            $this->setResult('warning', [
                'message'      => 'Đã điểm danh trước đó',
                'student_name' => $student->full_name,
                'saint_name'   => $student->saint_name,
                'time'         => $record->created_at?->format('H:i'),
            ]);

            return;
        }

        app(\App\Services\AttendanceService::class)->logCreatedRecord(
            $record,
            $class->parish_id ?? null
        );

        array_unshift($this->scannedLog, [
            'student_name' => $student->full_name,
            'saint_name'   => $student->saint_name,
            'class_name'   => $class->name,
            'time'         => now()->format('H:i'),
            'kind'         => 'student',
        ]);

        $this->setResult('success', [
            'message'      => 'Điểm danh thành công!',
            'student_name' => $student->full_name,
            'saint_name'   => $student->saint_name,
            'class_name'   => $class->name,
        ]);
    }

    protected function handleTeacherQr(Teacher $teacher): void
    {
        if (! $teacher->is_active) {
            $this->setResult('error', [
                'message'      => 'Giáo lý viên đã nghỉ',
                'student_name' => $teacher->full_name,
            ]);

            return;
        }

        if ($this->parishId && (int) $teacher->parish_id !== (int) $this->parishId) {
            $this->setResult('error', [
                'message'      => 'GLV không thuộc giáo xứ của bạn',
                'student_name' => $teacher->full_name_with_saint,
            ]);

            return;
        }

        $yearId = app(SchoolYearResolver::class)->resolveId($this->parishId ? (int) $this->parishId : null);

        if (! $yearId) {
            $this->setResult('error', [
                'message'      => 'Chưa xác định được năm học',
                'student_name' => $teacher->full_name_with_saint,
            ]);

            return;
        }

        $type = in_array((int) $this->type, [
            TeacherAttendanceSession::TYPE_TEACH,
            TeacherAttendanceSession::TYPE_CEREMONY,
            TeacherAttendanceSession::TYPE_MEETING,
        ], true) ? (int) $this->type : TeacherAttendanceSession::TYPE_TEACH;

        $session = TeacherAttendanceSession::query()
            ->where('parish_id', $this->parishId)
            ->where('namhoc_id', $yearId)
            ->where('type', $type)
            ->whereDate('date', today())
            ->where('status', TeacherAttendanceSession::STATUS_OPENING)
            ->first();

        if (! $session) {
            $this->setResult('error', [
                'message'      => 'Hôm nay chưa có buổi ' . TeacherAttendanceSession::typeLabel($type) . ' (GLV) đang mở',
                'student_name' => $teacher->full_name_with_saint,
            ]);

            return;
        }

        $record = TeacherAttendanceRecord::firstOrCreate(
            [
                'session_id' => $session->id,
                'teacher_id' => $teacher->id,
            ],
            [
                'status'     => TeacherAttendanceRecord::STATUS_PRESENT,
                'note'       => '',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]
        );

        array_unshift($this->scannedLog, [
            'student_name' => $teacher->full_name,
            'saint_name'   => $teacher->saint?->name ?? 'GLV',
            'class_name'   => TeacherAttendanceSession::typeLabel($type),
            'time'         => now()->format('H:i'),
            'kind'         => 'teacher',
        ]);

        if (! $record->wasRecentlyCreated) {
            $this->setResult('warning', [
                'message'      => 'GLV đã điểm danh trước đó',
                'student_name' => $teacher->full_name_with_saint,
                'class_name'   => TeacherAttendanceSession::typeLabel($type),
                'time'         => $record->created_at?->format('H:i'),
            ]);

            return;
        }

        $this->setResult('success', [
            'message'      => 'Điểm danh GLV thành công!',
            'student_name' => $teacher->full_name_with_saint,
            'class_name'   => TeacherAttendanceSession::typeLabel($type),
        ]);
    }

    // ==================== HELPERS ====================

    private function setResult(string $type, array $data): void
    {
        $this->lastResultType = $type;
        $this->lastResult     = $data;
    }

    private function isUuidToken(string $token): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $token
        );
    }

    // ==================== RENDER ====================

    public function render()
    {
        $layout = auth()->user()?->usesCatechistLayout()
            ? 'frontend.layout.catechist'
            : 'frontend.layout.main';

        return view('livewire.attendance.attendance-qr')
            ->extends($layout)
            ->section('content');
    }
}
