<?php

namespace App\Services;

use App\Models\AttendanceEditLog;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ParishNew;
use App\Models\StudentsClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Chốt điểm danh: học sinh đang học nhưng chưa có bản ghi → vắng không phép,
 * rồi khóa buổi. Chỉ áp dụng khi buổi đã có ít nhất một người được điểm danh.
 */
class AttendanceFinalizer
{
    public const AUTO_NOTE = 'Tự động chốt';

    /**
     * Chạy cho mọi giáo xứ đã đến giờ chốt.
     *
     * @return array{parishes: int, sessions: int, marked_absent: int}
     */
    public function finalizeDueParishes(?Carbon $now = null): array
    {
        $now = $now?->copy() ?? now();
        $totals = ['parishes' => 0, 'sessions' => 0, 'marked_absent' => 0];

        ParishNew::query()
            ->where('attendance_auto_finalize_enabled', true)
            ->orderBy('id')
            ->each(function (ParishNew $parish) use ($now, &$totals) {
                $result = $this->finalizeParish($parish, $now);
                if ($result['sessions'] > 0 || $result['marked_absent'] > 0) {
                    $totals['parishes']++;
                }
                $totals['sessions'] += $result['sessions'];
                $totals['marked_absent'] += $result['marked_absent'];
            });

        return $totals;
    }

    /**
     * Chốt các buổi đang mở của một giáo xứ (ngày hôm nay và các ngày trước còn mở).
     *
     * @return array{sessions: int, marked_absent: int, skipped_empty: int}
     */
    public function finalizeParish(ParishNew $parish, ?Carbon $now = null): array
    {
        $now = $now?->copy() ?? now();
        $today = $now->toDateString();

        $sessions = AttendanceSession::query()
            ->where('status', AttendanceSession::STATUS_OPENING)
            ->whereDate('date', '<=', $today)
            ->whereHas('catechismClass', fn ($q) => $q->where('parish_id', $parish->id))
            ->with('catechismClass:id,parish_id,name')
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $closed = 0;
        $marked = 0;
        $skippedEmpty = 0;

        foreach ($sessions as $session) {
            // Buổi hôm nay: chỉ chốt khi đã tới giờ cấu hình.
            if ($session->date->toDateString() === $today
                && ! $this->isPastFinalizeTime($parish, $now)
            ) {
                continue;
            }

            $result = $this->finalizeSession($session);

            if (($result['skipped_empty'] ?? false) === true) {
                $skippedEmpty++;
                continue;
            }

            if ($result['closed']) {
                $closed++;
                $marked += $result['marked_absent'];
            }
        }

        return [
            'sessions'       => $closed,
            'marked_absent'  => $marked,
            'skipped_empty'  => $skippedEmpty,
        ];
    }

    /**
     * Chốt một buổi: đánh vắng không phép cho HS chưa có bản ghi (nếu buổi đã có dữ liệu), rồi khóa.
     *
     * @return array{closed: bool, marked_absent: int, skipped_empty?: bool, reason?: string}
     */
    public function finalizeSession(AttendanceSession $session, ?int $actorId = null): array
    {
        if ((int) $session->status !== AttendanceSession::STATUS_OPENING) {
            return [
                'closed'        => false,
                'marked_absent' => 0,
                'reason'        => 'Buổi không đang mở',
            ];
        }

        $checkedCount = AttendanceRecord::query()
            ->where('session_id', $session->id)
            ->whereNotNull('status')
            ->count();

        // Chưa ai điểm danh → không tự đánh vắng cả lớp (có thể buổi chưa diễn ra / quên điểm danh).
        if ($checkedCount === 0) {
            return [
                'closed'         => false,
                'marked_absent'  => 0,
                'skipped_empty'  => true,
                'reason'         => 'Buổi chưa có ai được điểm danh',
            ];
        }

        $classId = (int) $session->class_id;
        $enrolledIds = StudentsClass::query()
            ->where('class_id', $classId)
            ->where('status', StudentsClass::STATUS_ENROLLED)
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $existingIds = AttendanceRecord::query()
            ->where('session_id', $session->id)
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $missingIds = array_values(array_diff($enrolledIds, $existingIds));
        $marked = 0;
        $now = now();
        $batchId = (string) Str::uuid();
        $parishId = $session->catechismClass?->parish_id
            ?? $session->catechismClass()->value('parish_id');

        DB::transaction(function () use (
            $session,
            $missingIds,
            $actorId,
            $now,
            $batchId,
            $parishId,
            &$marked
        ) {
            if ($missingIds !== []) {
                $rows = [];
                $logs = [];

                foreach ($missingIds as $studentId) {
                    $rows[] = [
                        'session_id' => $session->id,
                        'student_id' => $studentId,
                        'status'     => AttendanceRecord::STATUS_ABSENT_UNEXCUSED,
                        'note'       => self::AUTO_NOTE,
                        'created_by' => $actorId,
                        'updated_by' => $actorId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if ($parishId) {
                        $logs[] = [
                            'batch_id'             => $batchId,
                            'parish_id'            => $parishId,
                            'session_id'           => $session->id,
                            'student_id'           => $studentId,
                            'attendance_record_id' => null,
                            'old_status'           => null,
                            'new_status'           => AttendanceRecord::STATUS_ABSENT_UNEXCUSED,
                            'old_note'             => null,
                            'new_note'             => self::AUTO_NOTE,
                            'action'               => AttendanceEditLog::ACTION_CREATED,
                            'user_id'              => $actorId,
                            'created_at'           => $now,
                            'updated_at'           => $now,
                        ];
                    }
                }

                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::table('attendance_records')->insert($chunk);
                }

                if ($logs !== []) {
                    foreach (array_chunk($logs, 500) as $chunk) {
                        DB::table('attendance_edit_logs')->insert($chunk);
                    }
                }

                $marked = count($missingIds);
            }

            $session->close();
        });

        Log::info('Attendance session auto-finalized', [
            'session_id'    => $session->id,
            'class_id'      => $classId,
            'date'          => $session->date?->toDateString(),
            'marked_absent' => $marked,
        ]);

        return [
            'closed'        => true,
            'marked_absent' => $marked,
        ];
    }

    public function isPastFinalizeTime(ParishNew $parish, Carbon $now): bool
    {
        if (! $parish->attendance_auto_finalize_enabled) {
            return false;
        }

        [$hour, $minute] = array_map(
            'intval',
            explode(':', $parish->attendanceAutoFinalizeTimeHi())
        );

        $cutoff = $now->copy()->setTime($hour, $minute, 0);

        return $now->greaterThanOrEqualTo($cutoff);
    }
}
