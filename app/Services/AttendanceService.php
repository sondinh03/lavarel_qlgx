<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    public function saveBulkAttendance(array $drafts): array
    {
        DB::beginTransaction();

        try {
            $savedCount = 0;
            $errors     = [];
            $userId     = auth()->id();
            $now        = now();

            $groupedBySession = collect($drafts)->groupBy('session_id');

            foreach ($groupedBySession as $sessionId => $records) {
                $session = AttendanceSession::find($sessionId);

                if (!$session) {
                    $errors[] = "Buổi #{$sessionId} không tồn tại";
                    continue;
                }

                $canEdit = $session->canEdit();
                if (!$canEdit['can']) {
                    $errors[] = "Buổi {$session->date->format('d/m')}: {$canEdit['reason']}";
                    continue;
                }

                $firstRecord = $records->first();
                if (
                    isset($firstRecord['attendanceType'])
                    && (int) $session->type !== (int) $firstRecord['attendanceType']
                ) {
                    $errors[] = "Buổi {$session->date->format('d/m')}: Sai loại điểm danh";
                    continue;
                }

                // ✅ 1 query lấy tất cả records hiện có của session này
                $existingIds = AttendanceRecord::where('session_id', $sessionId)
                    ->pluck('id', 'student_id')  // [student_id => record_id]
                    ->toArray();

                $toInsert = [];
                $toUpdate = [];

                foreach ($records as $record) {
                    if (!AttendanceRecord::isValidStatus($record['status'])) {
                        $errors[] = "Học sinh #{$record['student_id']}: Trạng thái không hợp lệ";
                        continue;
                    }

                    $studentId = $record['student_id'];

                    if (isset($existingIds[$studentId])) {
                        // Có rồi → UPDATE
                        $toUpdate[] = [
                            'id'         => $existingIds[$studentId],
                            'status'     => $record['status'],
                            'note'       => $record['note'] ?? null,
                            'updated_by' => $userId,
                            'updated_at' => $now,
                        ];
                    } else {
                        // Chưa có → INSERT
                        $toInsert[] = [
                            'session_id' => $sessionId,
                            'student_id' => $studentId,
                            'status'     => $record['status'],
                            'note'       => $record['note'] ?? null,
                            'created_by' => $userId,
                            'updated_by' => $userId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    $savedCount++;
                }

                // ✅ Bulk INSERT 1 query thay vì N queries
                if (!empty($toInsert)) {
                    DB::table('attendance_records')->insert($toInsert);
                }

                // ✅ Bulk UPDATE dùng CASE WHEN — 1 query thay vì N queries
                if (!empty($toUpdate)) {
                    $ids          = array_column($toUpdate, 'id');
                    $statusCases  = '';
                    $noteCases    = '';
                    $bindings     = [];

                    foreach ($toUpdate as $row) {
                        $statusCases .= " WHEN id = ? THEN ?";
                        $noteCases   .= " WHEN id = ? THEN ?";
                        $bindings[]   = $row['id'];
                        $bindings[]   = $row['status'];
                    }
                    foreach ($toUpdate as $row) {
                        $bindings[] = $row['id'];
                        $bindings[] = $row['note'];
                    }

                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $bindings     = array_merge(
                        array_column($toUpdate, 'id'),
                        array_column($toUpdate, 'status'),
                        array_column($toUpdate, 'id'),
                        array_column($toUpdate, 'note'),
                        [$userId, $now],
                        $ids
                    );

                    DB::statement("
                    UPDATE attendance_records
                    SET
                        status     = CASE " . implode(' ', array_map(
                        fn($r) => "WHEN id = {$r['id']} THEN {$r['status']}",
                        $toUpdate
                    )) . " END,
                        note       = CASE " . implode(' ', array_map(
                        fn($r) => "WHEN id = {$r['id']} THEN " . DB::getPdo()->quote($r['note'] ?? ''),
                        $toUpdate
                    )) . " END,
                        updated_by = ?,
                        updated_at = ?
                    WHERE id IN ({$placeholders})
                ", array_merge([$userId, $now], $ids));
                }
            }

            DB::commit();

            if ($savedCount === 0 && !empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Không lưu được bản ghi nào. ' . implode('; ', array_slice($errors, 0, 3)),
                ];
            }

            $message = "Đã lưu {$savedCount} bản ghi điểm danh";
            if (!empty($errors)) {
                $message .= sprintf(' (có %d lỗi)', count($errors));
            }

            return ['success' => true, 'message' => $message, 'errors' => $errors];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('AttendanceService::saveBulkAttendance failed', [
                'drafts_count' => count($drafts),
                'error'        => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Có lỗi khi lưu điểm danh. Vui lòng thử lại sau.'];
        }
    }
}
