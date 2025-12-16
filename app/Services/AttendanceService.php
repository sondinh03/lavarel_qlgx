<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Set attendance record cho 1 học sinh
     */
    public function setAttendanceRecord($classId, $studentId, $sessionId, $type, $status)
    {
        DB::beginTransaction();

        try {
            // Tìm hoặc tạo session
            $session = AttendanceSession::findOrFail($sessionId);

            // Tìm record hiện tại
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
        $query = AttendanceSession::where('class_id', $classId)->whereBetween('date', [$startDate, $endDate]);

        if ($type) {
            $query = $query->with('type', $type);
        }

        $sessions = $query->with('records')->get();

        $statistics = [
            'total_sessions' => $sessions->count(),
            'students' => [],
        ];

        $students = Student::where('class_id', $classId)
            ->where('status', 1)
            ->get();

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

        $sessions = AttendanceSession::where('class_id', $student->class_id)
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
                    'date' => $session->session_date,
                    'type' => $session->type,
                    'status' => $record?->status,
                    'note' => $record?->note,
                ];
            }),
        ];
    }

    /**
     * Bulk import attendance
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

            AttendanceRecord::insert($records);

            DB::commit();
            return ['success' => true];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function saveAttendance($sessionId, $records, $username = 'system')
    {
        foreach ($records as $studentId => $status) {
            AttendanceRecord::updateOrCreate(
                [
                    'session_id' => $sessionId,
                    'student_id' => $studentId,
                ],
                [
                    'status' => $status,
                    'updated_by' => $username,
                ]
            );
        }

        return true;
    }

    public function saveBulkAttendance(array $draft) {
        DB::beginTransaction();

        try {
            foreach ($draft as $item) {
                $session = AttendanceSession::findOrFail($item['session_id']);

                // CHốt nghiệp vụ
                if ($session->type != $item['attendanceType']) {
                    throw new \Exception('Sai loại điểm danh');
                }

                AttendanceRecord::updateOrCreate(
                    [
                        'session_id' => $item['session_id'],
                        'student_id' => $item['student_id'],
                    ],
                    [
                        'status' => $item['status'],
                        'updated_by' => auth()->id(),
                        'created_by' => auth()->id(),
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
     * Export attendance to Excel
     */
    public function exportToExcel($classId, $startDate, $endDate)
    {
        // Implementation using Laravel Excel or similar
        // Return Excel file
    }
}
