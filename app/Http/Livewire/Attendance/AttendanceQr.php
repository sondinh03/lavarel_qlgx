<?php

namespace App\Http\Livewire\Attendance;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\StudentNew;
use Illuminate\Support\Facades\Log;

class AttendanceQr extends BaseComponent
{
     // ==================== STATE ====================

    /** @var int 1=học, 2=lễ */
    public int $type = 1;

    /** @var array|null Kết quả scan gần nhất */
    public $lastResult = null;

    /** @var string 'success'|'warning'|'error'|null */
    public $lastResultType = null;

    /** @var array Danh sách đã điểm danh trong session này (in-memory) */
    public array $scannedLog = [];

    /** GLV chưa có phân công trong năm học đang vận hành → chặn quét */
    public bool $assignmentBlocked = false;

    // ==================== LISTENERS ====================

    protected $listeners = [
        'qrScanned' => 'handleQrScanned',
    ];

    // ==================== LIFECYCLE ====================

    public function mount()
    {
        parent::mount();
        $this->requireParishId();

        $user = auth()->user();
        if ($user && $user->isCatechist() && ! $user->canManage()
            && ! app(\App\Services\CatechistAccess::class)
                ->hasActiveAssignmentThisYear($user, $this->parishId)
        ) {
            $this->assignmentBlocked = true;
        }
    }

    protected function loadInitialData(): void
    {
        // Không có data cần load trước — session được resolve lúc quét
    }

    // ==================== ACTIONS ====================

    public function setType(int $type): void
    {
        $this->type       = $type;
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
     * $token = qr_token của học sinh
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
            if (!$this->isStudentQrToken($token)) {
                $this->setResult('error', ['message' => 'Không phải thẻ học sinh']);
                return;
            }

            // 1. Tìm học sinh theo qr_token trong giáo xứ hiện tại
            $student = StudentNew::where('qr_token', $token)
                ->when($this->parishId, fn ($q) => $q->where('parish_id', $this->parishId))
                ->first();
            Log::info('Student found', ['student' => $student?->id]);

            if (!$student) {
                $this->setResult('error', ['message' => 'Không tìm thấy học sinh']);
                return;
            }

            if ($this->parishId && (int) $student->parish_id !== (int) $this->parishId) {
                $this->setResult('error', ['message' => 'Học sinh không thuộc giáo xứ của bạn']);
                return;
            }

            // 2. Lấy lớp hiện tại của học sinh
            $class = $student->classes()
                ->where('classes.parish_id', $this->parishId)
                ->wherePivot('status', 1)
                ->first();
            Log::info('Class found', ['class' => $class?->id, 'name' => $class?->name]);

            if (!$class) {
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

            // 3. Tìm session hôm nay của lớp theo type đang chọn
            $session = AttendanceSession::where('class_id', $class->id)
                ->where('type', $this->type)
                ->whereDate('date', today())
                ->where('status', '!=', AttendanceSession::STATUS_CLOSED)
                ->first();

            if (!$session) {
                $typeLabel = $this->type === 1 ? 'học' : 'lễ';
                $this->setResult('error', [
                    'message'      => "Lớp {$class->name} hôm nay không có buổi {$typeLabel}",
                    'saint_name'   => $student->saint_name,
                    'student_name' => $student->full_name,
                ]);
                return;
            }

            // 4. Điểm danh — firstOrCreate tránh race condition
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

            if (!$record->wasRecentlyCreated) {
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

            // 5. Thêm vào in-memory log
            array_unshift($this->scannedLog, [
                'student_name' => $student->full_name,
                'saint_name'   => $student->saint_name,
                'class_name'   => $class->name,
                'time'         => now()->format('H:i'),
            ]);

            $this->setResult('success', [
                'message'      => 'Điểm danh thành công!',
                'student_name' => $student->full_name,
                'saint_name'   => $student->saint_name,
                'class_name'   => $class->name,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] === 1062) {
                // Unique constraint violation — race condition cực hiếm
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

    // ==================== HELPERS ====================

    private function setResult(string $type, array $data): void
    {
        $this->lastResultType = $type;
        $this->lastResult     = $data;
    }

    private function isStudentQrToken(string $token): bool
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
