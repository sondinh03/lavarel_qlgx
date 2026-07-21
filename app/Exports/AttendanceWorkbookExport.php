<?php

namespace App\Exports;

use App\Models\AttendanceSession;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceWorkbookExport implements WithMultipleSheets
{
    public function __construct(private int $classId) {}

    public function sheets(): array
    {
        return [
            new AttendanceExport($this->classId, null, AttendanceSession::TYPE_CLASS),
            new AttendanceExport($this->classId, null, AttendanceSession::TYPE_CEREMONY),
        ];
    }
}
