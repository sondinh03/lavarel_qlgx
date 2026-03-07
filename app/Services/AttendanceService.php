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

            // Group by session để validate session 1 lần
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

                // Validate type khớp
                $firstRecord = $records->first();
                if (
                    isset($firstRecord['attendanceType'])
                    && (int) $session->type !== (int) $firstRecord['attendanceType']
                ) {
                    $errors[] = "Buổi {$session->date->format('d/m')}: Sai loại điểm danh";
                    continue;
                }

                foreach ($records as $record) {
                    if (!AttendanceRecord::isValidStatus($record['status'])) {
                        $errors[] = "Học sinh #{$record['student_id']}: Trạng thái không hợp lệ";
                        continue;
                    }

                    // Dùng updateOrCreate — tách created_by và updated_by đúng ngữ nghĩa
                    $existing = AttendanceRecord::where('session_id', $sessionId)
                        ->where('student_id', $record['student_id'])
                        ->first();

                    if ($existing) {
                        $existing->update([
                            'status'     => $record['status'],
                            'note'       => $record['note'] ?? null,
                            'updated_by' => $userId,
                        ]);
                    } else {
                        AttendanceRecord::create([
                            'session_id' => $sessionId,
                            'student_id' => $record['student_id'],
                            'status'     => $record['status'],
                            'note'       => $record['note'] ?? null,
                            'created_by' => $userId,
                            'updated_by' => $userId,
                        ]);
                    }

                    $savedCount++;
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
                'trace'        => $e->getTraceAsString(),
            ]);

            return ['success' => false, 'message' => 'Có lỗi khi lưu điểm danh. Vui lòng thử lại sau.'];
        }
    }
}
