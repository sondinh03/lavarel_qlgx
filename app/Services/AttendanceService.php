<?php

namespace App\Services;

use App\Models\AttendanceEditLog;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AttendanceService
{
    public function saveBulkAttendance(array $drafts, ?int $classId = null, ?int $type = null): array
    {
        DB::beginTransaction();

        try {
            $savedCount = 0;
            $savedKeys  = [];
            $errors     = [];
            $logRows    = [];
            $batchId    = (string) Str::uuid();

            $groupedBySession = collect($drafts)->groupBy('session_id');

            foreach ($groupedBySession as $sessionId => $records) {
                $sessionQuery = AttendanceSession::query()
                    ->with('catechismClass:id,parish_id,name')
                    ->where('id', $sessionId);

                if ($classId !== null) {
                    $sessionQuery->where('class_id', $classId);
                }

                if ($type !== null) {
                    $sessionQuery->where('type', $type);
                }

                $session = $sessionQuery->first();

                if (! $session) {
                    $errors[] = "Buổi #{$sessionId} không hợp lệ với lớp đang chọn";
                    continue;
                }

                $canEdit = $session->canEdit();
                if (! $canEdit['can']) {
                    $errors[] = "Buổi {$session->date->format('d/m')}: {$canEdit['reason']}";
                    continue;
                }

                $parishId = $session->catechismClass?->parish_id;
                $existing = AttendanceRecord::where('session_id', $sessionId)
                    ->get()
                    ->keyBy('student_id');

                $toInsert = [];
                $toUpdate = [];

                foreach ($records as $record) {
                    $studentId = $record['student_id'];
                    // Lý do chỉ áp dụng khi vắng có phép
                    if ((int) ($record['status'] ?? 0) !== AttendanceRecord::STATUS_ABSENT_EXCUSED) {
                        $record['note'] = '';
                    }
                    if ($existing->has($studentId)) {
                        $toUpdate[] = $record + ['record_id' => $existing[$studentId]->id];
                    } else {
                        $toInsert[] = $record;
                    }
                }

                $userId = auth()->id();
                $now    = now();

                if (! empty($toInsert)) {
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

                    if ($parishId) {
                        foreach ($toInsert as $r) {
                            $logRows[] = [
                                'batch_id'             => $batchId,
                                'parish_id'            => $parishId,
                                'session_id'           => (int) $sessionId,
                                'student_id'           => (int) $r['student_id'],
                                'attendance_record_id' => null,
                                'old_status'           => null,
                                'new_status'           => (int) $r['status'],
                                'old_note'             => null,
                                'new_note'             => ($r['note'] ?? '') !== '' ? $r['note'] : null,
                                'action'               => AttendanceEditLog::ACTION_CREATED,
                                'user_id'              => $userId,
                                'created_at'           => $now,
                                'updated_at'           => $now,
                            ];
                        }
                    }
                }

                if (! empty($toUpdate)) {
                    foreach ($toUpdate as $r) {
                        $prev = $existing[$r['student_id']] ?? null;
                        $newNote = ($r['note'] ?? '') !== '' ? $r['note'] : null;
                        $oldNote = $prev?->note;
                        $oldStatus = $prev?->status !== null ? (int) $prev->status : null;
                        $newStatus = (int) $r['status'];

                        if ($parishId && ($oldStatus !== $newStatus || (string) $oldNote !== (string) $newNote)) {
                            $logRows[] = [
                                'batch_id'             => $batchId,
                                'parish_id'            => $parishId,
                                'session_id'           => (int) $sessionId,
                                'student_id'           => (int) $r['student_id'],
                                'attendance_record_id' => (int) $r['record_id'],
                                'old_status'           => $oldStatus,
                                'new_status'           => $newStatus,
                                'old_note'             => $oldNote,
                                'new_note'             => $newNote,
                                'action'               => AttendanceEditLog::ACTION_UPDATED,
                                'user_id'              => $userId,
                                'created_at'           => $now,
                                'updated_at'           => $now,
                            ];
                        }
                    }

                    $this->bulkUpdateRecords($toUpdate, $userId, $now);
                }

                $sessionSaved = count($toInsert) + count($toUpdate);
                $savedCount  += $sessionSaved;

                foreach (array_merge($toInsert, $toUpdate) as $r) {
                    $savedKeys[] = $r['student_id'] . '_' . $sessionId;
                }
            }

            if (! empty($logRows)) {
                DB::table('attendance_edit_logs')->insert($logRows);
            }

            DB::commit();

            if ($savedCount === 0 && ! empty($errors)) {
                return [
                    'success'   => false,
                    'message'   => 'Không lưu được. Kiểm tra lại các buổi đã chọn',
                    'errors'    => $errors,
                    'savedKeys' => [],
                ];
            }

            $message = ! empty($errors)
                ? 'Đã lưu một phần. Một số buổi bị bỏ qua'
                : 'Đã lưu điểm danh';

            return [
                'success'   => true,
                'message'   => $message,
                'errors'    => $errors,
                'savedKeys' => $savedKeys,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('saveBulkAttendance failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success'   => false,
                'message'   => 'Có lỗi khi lưu điểm danh',
                'errors'    => [],
                'savedKeys' => [],
            ];
        }
    }

    /**
     * Ghi nhật ký khi điểm danh bằng QR (tạo mới).
     */
    public function logCreatedRecord(AttendanceRecord $record, ?int $parishId = null): void
    {
        $parishId = $parishId
            ?? $record->session?->catechismClass?->parish_id
            ?? AttendanceSession::query()
                ->with('catechismClass:id,parish_id')
                ->find($record->session_id)
                ?->catechismClass
                ?->parish_id;

        if (! $parishId) {
            return;
        }

        AttendanceEditLog::create([
            'batch_id'             => (string) Str::uuid(),
            'parish_id'            => $parishId,
            'session_id'           => $record->session_id,
            'student_id'           => $record->student_id,
            'attendance_record_id' => $record->id,
            'old_status'           => null,
            'new_status'           => $record->status,
            'old_note'             => null,
            'new_note'             => $record->note,
            'action'               => AttendanceEditLog::ACTION_CREATED,
            'user_id'              => auth()->id(),
        ]);
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

        $statusBindings = [];
        $noteBindings   = [];

        foreach ($toUpdate as $r) {
            $recordId = (int) $r['record_id'];

            $statusSql .= ' WHEN ? THEN ?';
            $statusBindings[] = $recordId;
            $statusBindings[] = (int) $r['status'];

            $noteSql .= ' WHEN ? THEN ?';
            $noteBindings[] = $recordId;
            $noteBindings[] = ($r['note'] ?? '') !== '' ? $r['note'] : null;
        }

        $statusSql .= ' END';
        $noteSql   .= ' END';

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Thứ tự bindings PHẢI khớp đúng thứ tự placeholder xuất hiện trong SQL:
        // statusSql (N cặp) -> noteSql (N cặp) -> updated_by -> updated_at -> WHERE IN ids
        $bindings = array_merge(
            $statusBindings,
            $noteBindings,
            [$userId, $now],
            $ids
        );

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
