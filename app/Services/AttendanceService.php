<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Lưu điểm danh hàng loạt cho nhiều sessions
     * 
     * @param array $pendingAttendance ['session_id' => ['student_id' => status]]
     * @return array ['success' => bool, 'message' => string, 'saved_count' => int]
     */
    public function saveBulkAttendance(array $pendingAttendance)
    {
        DB::beginTransaction();

        try {
            $savedCount = 0;
            $errors = [];

            foreach ($pendingAttendance as $sessionId => $students) {
                try {
                    $result = $this->saveSessionAttendance($sessionId, $students);
                    $savedCount += $result['saved_count'];
                } catch (\Exception $e) {
                    $errors[] = "Session {$sessionId}: " . $e->getMessage();
                    Log::error('Error saving session attendance', [
                        'session_id' => $sessionId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . implode('; ', $errors),
                    'saved_count' => 0
                ];
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Đã lưu {$savedCount} bản ghi điểm danh",
                'saved_count' => $savedCount
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in saveBulkAttendance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'saved_count' => 0
            ];
        }
    }

    /**
     * Lưu điểm danh cho một session
     * 
     * @param int $sessionId
     * @param array $students ['student_id' => status]
     * @return array ['success' => bool, 'saved_count' => int]
     */
    public function saveSessionAttendance($sessionId, array $students)
    {
        $session = AttendanceSession::findOrFail($sessionId);

        // Kiểm tra session có bị khóa không
        if ($session->status == AttendanceSession::STATUS_CLOSED) {
            throw new \Exception("Session #{$sessionId} đã bị khóa");
        }

        // Lấy tất cả records hiện tại của session
        $existingRecords = AttendanceRecord::where('session_id', $sessionId)
            ->pluck('student_id')
            ->toArray();

        $recordsToUpdate = [];
        $recordsToCreate = [];
        $recordsToDelete = [];

        // Xác định records cần update/create
        foreach ($students as $studentId => $status) {
            $data = [
                'session_id' => $sessionId,
                'student_id' => $studentId,
                'status' => $status,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ];

            if (in_array($studentId, $existingRecords)) {
                $recordsToUpdate[] = $data;
            } else {
                $data['created_by'] = auth()->id();
                $data['created_at'] = now();
                $recordsToCreate[] = $data;
            }
        }

        // Xác định records cần xóa (có trong DB nhưng không có trong pending)
        $pendingStudentIds = array_keys($students);
        $recordsToDelete = array_diff($existingRecords, $pendingStudentIds);

        // Thực hiện xóa
        if (!empty($recordsToDelete)) {
            AttendanceRecord::where('session_id', $sessionId)
                ->whereIn('student_id', $recordsToDelete)
                ->delete();
        }

        // Thực hiện update
        foreach ($recordsToUpdate as $data) {
            AttendanceRecord::where('session_id', $sessionId)
                ->where('student_id', $data['student_id'])
                ->update([
                    'status' => $data['status'],
                    'updated_by' => $data['updated_by'],
                    'updated_at' => $data['updated_at'],
                ]);
        }

        // Thực hiện insert
        if (!empty($recordsToCreate)) {
            AttendanceRecord::insert($recordsToCreate);
        }

        return [
            'success' => true,
            'saved_count' => count($recordsToUpdate) + count($recordsToCreate)
        ];
    }

    /**
     * Set attendance record cho 1 học sinh
     */
    public function setAttendanceRecord($classId, $studentId, $sessionId, $type, $status)
    {
        DB::beginTransaction();

        try {
            $session = AttendanceSession::findOrFail($sessionId);

            $record = AttendanceRecord::where('session_id', $session->id)
                ->where('student_id', $studentId)
                ->first();

            if ($status === null) {
                if ($record) {
                    $record->delete();
                }
            } else {
                AttendanceRecord::updateOrCreate(
                    [
                        'session_id' => $session->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'status' => $status,
                        'updated_by' => auth()->id(),
                        'created_by' => $record ? $record->created_by : auth()->id(),
                    ]
                );
            }

            DB::commit();
            return ['success' => true];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }


    /**
     * Get attendance statistics for a class
     */
    public function getClassStatistics($classId, $startDate, $endDate, $type = null)
    {
        $query = AttendanceSession::where('class_id', $classId)
            ->whereBetween('date', [$startDate, $endDate]);

        if ($type) {
            $query->where('type', $type);
        }

        $sessions = $query->with('records')->get();

        $statistics = [
            'total_sessions' => $sessions->count(),
            'students' => [],
        ];

        $students = Student::whereHas('classes', function ($q) use ($classId) {
            $q->where('lops.id', $classId)
                ->wherePivot('status', 1);
        })->get();

        foreach ($students as $student) {
            $present = 0;
            $absent_excused = 0;
            $absent_unexcused = 0;
            $not_recorded = 0;

            foreach ($sessions as $session) {
                $record = $session->records->firstWhere('student_id', $student->id);

                if (!$record) {
                    $not_recorded++;
                } elseif ($record->status == AttendanceRecord::STATUS_PRESENT) {
                    $present++;
                } elseif ($record->status == AttendanceRecord::STATUS_ABSENT_EXCUSED) {
                    $absent_excused++;
                } elseif ($record->status == AttendanceRecord::STATUS_ABSENT_UNEXCUSED) {
                    $absent_unexcused++;
                }
            }

            $statistics['students'][] = [
                'id' => $student->id,
                'name' => "{$student->saint_name} {$student->last_name} {$student->name}",
                'present' => $present,
                'absent_excused' => $absent_excused,
                'absent_unexcused' => $absent_unexcused,
                'not_recorded' => $not_recorded,
                'attendance_rate' => $statistics['total_sessions'] > 0
                    ? round(($present / $statistics['total_sessions']) * 100, 1)
                    : 0,
            ];
        }

        return $statistics;
    }

    /**
     * Get student attendance history
     */
    public function getStudentHistory($studentId, $startDate, $endDate)
    {
        $student = Student::findOrFail($studentId);

        // Lấy class_id từ pivot table
        $classId = $student->classes()->wherePivot('status', 1)->first()?->id;

        if (!$classId) {
            throw new \Exception('Student is not assigned to any active class');
        }

        $sessions = AttendanceSession::where('class_id', $classId)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['records' => function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            }])
            ->orderBy('date')
            ->get();

        return [
            'student' => $student,
            'sessions' => $sessions->map(function ($session) {
                $record = $session->records->first();
                return [
                    'date' => $session->date,
                    'type' => $session->type,
                    'status' => $record?->status,
                    'note' => $record?->note,
                ];
            }),
        ];
    }

    /**
     * Bulk import attendance by session ID
     */
    public function bulkImportBySessionId($sessionId, array $attendanceData)
    {
        DB::beginTransaction();

        try {
            $session = AttendanceSession::findOrFail($sessionId);

            // Xóa toàn bộ record cũ
            AttendanceRecord::where('session_id', $session->id)->delete();

            $records = [];
            foreach ($attendanceData as $data) {
                $records[] = [
                    'session_id' => $session->id,
                    'student_id' => $data['student_id'],
                    'status' => $data['status'],
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($records)) {
                AttendanceRecord::insert($records);
            }

            DB::commit();
            return ['success' => true, 'saved_count' => count($records)];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Export attendance to Excel
     */
    public function exportToExcel($classId, $startDate, $endDate)
    {
        // Implementation using Laravel Excel or similar
        // Return Excel file
    }
}
