<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    public function saveBulkAttendance(array $drafts, ?int $classId = null, ?int $type = null): array
    {
        DB::beginTransaction();

        try {
            $savedCount = 0;
            $savedKeys  = [];
            $errors     = [];

            $groupedBySession = collect($drafts)->groupBy('session_id');

            foreach ($groupedBySession as $sessionId => $records) {
                $sessionQuery = AttendanceSession::query()->where('id', $sessionId);

                if ($classId !== null) {
                    $sessionQuery->where('class_id', $classId);
                }

                if ($type !== null) {
                    $sessionQuery->where('type', $type);
                }

                $session = $sessionQuery->first();

                if (!$session) {
                    $errors[] = "Buổi #{$sessionId} không hợp lệ với lớp đang chọn";
                    continue;
                }

                $canEdit = $session->canEdit();
                if (!$canEdit['can']) {
                    $errors[] = "Buổi {$session->date->format('d/m')}: {$canEdit['reason']}";
                    continue;
                }

                $existingIds = AttendanceRecord::where('session_id', $sessionId)
                    ->pluck('id', 'student_id')
                    ->toArray();

                $toInsert = [];
                $toUpdate = [];

                foreach ($records as $record) {
                    $studentId = $record['student_id'];
                    if (isset($existingIds[$studentId])) {
                        $toUpdate[] = $record + ['record_id' => $existingIds[$studentId]];
                    } else {
                        $toInsert[] = $record;
                    }
                }

                $userId = auth()->id();
                $now    = now();

                if (!empty($toInsert)) {
                    DB::table('attendance_records')->insert(
                        collect($toInsert)->map(fn ($r) => [
                            'session_id' => $sessionId,
                            'student_id' => $r['student_id'],
                            'status'     => $r['status'],
                            'note'       => $r['note'] !== '' ? $r['note'] : null,
                            'created_by' => $userId,
                            'updated_by' => $userId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ])->toArray()
                    );
                }

                if (!empty($toUpdate)) {
                    $this->bulkUpdateRecords($toUpdate, $userId, $now);
                }

                $sessionSaved = count($toInsert) + count($toUpdate);
                $savedCount  += $sessionSaved;

                foreach (array_merge($toInsert, $toUpdate) as $r) {
                    $savedKeys[] = $r['student_id'] . '_' . $sessionId;
                }
            }

            DB::commit();

            if ($savedCount === 0 && !empty($errors)) {
                return [
                    'success'   => false,
                    'message'   => 'Không có học sinh nào được lưu. ' . implode('; ', array_slice($errors, 0, 3)),
                    'errors'    => $errors,
                    'savedKeys' => [],
                ];
            }

            $message = "Đã lưu điểm danh cho {$savedCount} học sinh";
            if (!empty($errors)) {
                $message .= sprintf(' (bỏ qua %d buổi: %s)', count($errors), implode('; ', array_slice($errors, 0, 2)));
            }

            return [
                'success'   => true,
                'message'   => $message,
                'errors'    => $errors,
                'savedKeys' => $savedKeys,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('saveBulkAttendance failed', ['error' => $e->getMessage()]);

            return [
                'success'   => false,
                'message'   => 'Có lỗi khi lưu điểm danh. Vui lòng thử lại sau.',
                'errors'    => [],
                'savedKeys' => [],
            ];
        }
    }

    /**
     * Cập nhật nhiều record trong 1–2 query thay vì N lần Eloquent.
     */
    private function bulkUpdateRecords(array $toUpdate, $userId, $now): void
    {
        $ids = collect($toUpdate)->pluck('record_id')->map(fn ($id) => (int) $id)->all();

        if (empty($ids)) {
            return;
        }

        $statusSql = 'CASE id';
        $noteSql   = 'CASE id';
        $bindings  = [];

        foreach ($toUpdate as $r) {
            $statusSql .= ' WHEN ? THEN ?';
            $bindings[] = (int) $r['record_id'];
            $bindings[] = (int) $r['status'];

            $noteSql .= ' WHEN ? THEN ?';
            $bindings[] = (int) $r['record_id'];
            $bindings[] = ($r['note'] ?? '') !== '' ? $r['note'] : null;
        }

        $statusSql .= ' END';
        $noteSql   .= ' END';

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $bindings[]   = $userId;
        $bindings[]   = $now;
        $bindings     = array_merge($bindings, $ids);

        DB::update(
            "UPDATE attendance_records
             SET status = {$statusSql},
                 note = {$noteSql},
                 updated_by = ?,
                 updated_at = ?
             WHERE id IN ({$placeholders})",
            $bindings
        );
    }
}
