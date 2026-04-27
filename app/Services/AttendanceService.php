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

                // INSERT
                if (!empty($toInsert)) {
                    $userId = auth()->id();
                    $now    = now();
                    DB::table('attendance_records')->insert(
                        collect($toInsert)->map(fn($r) => [
                            'session_id' => $sessionId,
                            'student_id' => $r['student_id'],
                            'status'     => $r['status'],
                            'note'       => $r['note'] ?? null,
                            'created_by' => $userId,
                            'updated_by' => $userId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ])->toArray()
                    );
                }

                // UPDATE — dùng Eloquent upsert thay vì raw SQL
                if (!empty($toUpdate)) {
                    $userId = auth()->id();
                    $now    = now();
                    foreach ($toUpdate as $r) {
                        AttendanceRecord::where('id', $r['record_id'])->update([
                            'status'     => $r['status'],
                            'note'       => $r['note'] ?? null,
                            'updated_by' => $userId,
                            'updated_at' => $now,
                        ]);
                    }
                }

                $savedCount += count($toInsert) + count($toUpdate);
            }

            DB::commit();

            if ($savedCount === 0 && !empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Không có học sinh nào được lưu. ' . implode('; ', array_slice($errors, 0, 3)),
                ];
            }

            $message = "Đã lưu điểm danh cho {$savedCount} học sinh";
            if (!empty($errors)) {
                $message .= sprintf(' (có %d lỗi)', count($errors));
            }

            return ['success' => true, 'message' => $message, 'errors' => $errors];
        } catch (\Exception $e) {
            DB::rollBack();

            return ['success' => false, 'message' => 'Có lỗi khi lưu điểm danh. Vui lòng thử lại sau.'];
        }
    }
}
