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
        Log::info('💾 saveBulkAttendance() START', [
            'total_drafts' => count($drafts),
            'session_ids'  => collect($drafts)->pluck('session_id')->unique()->values(),
        ]);

        DB::beginTransaction();

        try {
            $savedCount = 0;
            $errors     = [];

            $groupedBySession = collect($drafts)->groupBy('session_id');

            foreach ($groupedBySession as $sessionId => $records) {
                Log::info('💾 processing session', [
                    'session_id'    => $sessionId,
                    'record_count'  => $records->count(),
                ]);

                $session = AttendanceSession::find($sessionId);

                if (!$session) {
                    Log::warning('💾 session not found', ['session_id' => $sessionId]);
                    $errors[] = "Buổi #{$sessionId} không tồn tại";
                    continue;
                }

                $canEdit = $session->canEdit();
                if (!$canEdit['can']) {
                    Log::warning('💾 session locked', ['session_id' => $sessionId, 'reason' => $canEdit['reason']]);
                    $errors[] = "Buổi {$session->date->format('d/m')}: {$canEdit['reason']}";
                    continue;
                }

                $existingIds = AttendanceRecord::where('session_id', $sessionId)
                    ->pluck('id', 'student_id')
                    ->toArray();

                Log::info('💾 existing records', [
                    'session_id' => $sessionId,
                    'existing'   => count($existingIds),
                ]);

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

                Log::info('💾 split insert/update', [
                    'session_id' => $sessionId,
                    'to_insert'  => count($toInsert),
                    'to_update'  => count($toUpdate),
                ]);

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
                    Log::info('💾 INSERT done', ['count' => count($toInsert)]);
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
                    Log::info('💾 UPDATE done', ['count' => count($toUpdate)]);
                }

                $savedCount += count($toInsert) + count($toUpdate);
            }

            DB::commit();

            Log::info('💾 saveBulkAttendance() SUCCESS', [
                'saved'  => $savedCount,
                'errors' => $errors,
            ]);

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

            Log::error('💾 saveBulkAttendance() FAILED', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);

            return ['success' => false, 'message' => 'Có lỗi khi lưu điểm danh. Vui lòng thử lại sau.'];
        }
    }
}
