<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ParishNew;
use App\Models\StudentsClass;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Nguồn sự thật duy nhất cho trạng thái điểm danh hiệu lực.
 *
 * Một ô chưa có bản ghi được suy luận là vắng không phép khi buổi đã có ít
 * nhất một người được điểm danh và buổi đã khóa hoặc đã qua giờ cắt của xứ.
 * Service chỉ suy luận khi đọc, không tạo AttendanceRecord.
 */
class AttendanceStatusResolver
{
    /**
     * @param  Collection<int, AttendanceSession>  $sessions
     * @param  Collection<int, int>|null  $studentIds
     * @return array<int, array<int, int|null>> [session_id => [student_id => effective status]]
     */
    public function matrix(
        Collection $sessions,
        ?Collection $studentIds = null,
        ?Carbon $at = null
    ): array {
        if ($sessions->isEmpty()) {
            return [];
        }

        $at = $at?->copy() ?? now();
        $sessions->each(
            fn (AttendanceSession $session) => $session->loadMissing('catechismClass.parish')
        );

        $classIds = $sessions->pluck('class_id')->map(fn ($id) => (int) $id)->unique()->values();
        $sessionIds = $sessions->pluck('id')->map(fn ($id) => (int) $id)->values();

        $memberships = StudentsClass::query()
            ->whereIn('class_id', $classIds)
            ->where('status', StudentsClass::STATUS_ENROLLED)
            ->when(
                $studentIds !== null,
                fn ($query) => $query->whereIn('student_id', $studentIds)
            )
            ->get(['class_id', 'student_id', 'created_at'])
            ->groupBy(fn ($membership) => (int) $membership->class_id);

        $records = AttendanceRecord::query()
            ->whereIn('session_id', $sessionIds)
            ->when(
                $studentIds !== null,
                fn ($query) => $query->whereIn('student_id', $studentIds)
            )
            ->get(['session_id', 'student_id', 'status'])
            ->groupBy(fn ($record) => (int) $record->session_id);

        $matrix = [];

        foreach ($sessions as $session) {
            $sessionId = (int) $session->id;
            $sessionRecords = $records->get($sessionId, collect());
            $recordMap = $sessionRecords->keyBy(fn ($record) => (int) $record->student_id);
            $hasAttendance = $sessionRecords->contains(
                fn ($record) => AttendanceRecord::isValidStatus($record->status)
            );
            $inferMissingAsUnexcused = $this->isConclusive($session, $hasAttendance, $at);

            foreach ($memberships->get((int) $session->class_id, collect()) as $membership) {
                $studentId = (int) $membership->student_id;
                $record = $recordMap->get($studentId);

                // Không suy luận vắng cho buổi trước ngày học sinh được xếp lớp.
                // Record thật (nếu có) vẫn luôn được tôn trọng, kể cả dữ liệu nhập cũ.
                if (! $record
                    && $membership->created_at
                    && $session->date
                    && $membership->created_at->copy()->startOfDay()->gt($session->date->copy()->endOfDay())
                ) {
                    continue;
                }

                $storedStatus = $record && AttendanceRecord::isValidStatus($record->status)
                    ? (int) $record->status
                    : null;

                $matrix[$sessionId][$studentId] = $storedStatus
                    ?? ($inferMissingAsUnexcused
                        ? AttendanceRecord::STATUS_ABSENT_UNEXCUSED
                        : null);
            }
        }

        return $matrix;
    }

    public function isConclusive(
        AttendanceSession $session,
        bool $hasAttendance,
        ?Carbon $at = null
    ): bool {
        if (! $hasAttendance || (int) $session->status === AttendanceSession::STATUS_CANCELLED) {
            return false;
        }

        if ((int) $session->status === AttendanceSession::STATUS_CLOSED) {
            return true;
        }

        $parish = $session->catechismClass?->parish;

        return $parish instanceof ParishNew
            && (bool) $parish->attendance_auto_finalize_enabled
            && $this->isPastCutoff($session, $parish, $at);
    }

    public function isPastCutoff(
        AttendanceSession $session,
        ParishNew $parish,
        ?Carbon $at = null
    ): bool {
        if (! $session->date) {
            return false;
        }

        $at = $at?->copy() ?? now();
        [$hour, $minute] = array_map('intval', explode(':', $parish->attendanceAutoFinalizeTimeHi()));
        $cutoff = $session->date->copy()->setTime($hour, $minute);

        return $at->greaterThanOrEqualTo($cutoff);
    }

    /**
     * Các buổi đã có dữ liệu nhưng chưa khóa và chưa tới giờ cắt.
     *
     * @param  Collection<int, AttendanceSession>  $sessions
     * @return Collection<int, AttendanceSession>
     */
    public function pendingSessions(Collection $sessions, ?Carbon $at = null): Collection
    {
        if ($sessions->isEmpty()) {
            return collect();
        }

        $at = $at?->copy() ?? now();
        $sessions->each(
            fn (AttendanceSession $session) => $session->loadMissing('catechismClass.parish')
        );
        $recordedSessionIds = AttendanceRecord::query()
            ->whereIn('session_id', $sessions->pluck('id'))
            ->whereIn('status', [
                AttendanceRecord::STATUS_PRESENT,
                AttendanceRecord::STATUS_ABSENT_EXCUSED,
                AttendanceRecord::STATUS_ABSENT_UNEXCUSED,
            ])
            ->distinct()
            ->pluck('session_id')
            ->map(fn ($id) => (int) $id)
            ->flip();

        return $sessions
            ->filter(function (AttendanceSession $session) use ($recordedSessionIds, $at) {
                $hasAttendance = $recordedSessionIds->has((int) $session->id);

                return $hasAttendance && ! $this->isConclusive($session, true, $at);
            })
            ->values();
    }

    public function cutoffLabel(?ParishNew $parish): string
    {
        return $parish?->attendanceAutoFinalizeTimeHi()
            ?? ParishNew::DEFAULT_ATTENDANCE_AUTO_FINALIZE_TIME;
    }
}
