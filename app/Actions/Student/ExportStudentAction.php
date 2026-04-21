<?php

namespace App\Actions\Student;

use App\Exports\StudentExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportStudentAction
{
    public function handle(int $classId, string $format = 'xlsx'): BinaryFileResponse
    {
        $filename = 'danh-sach-hoc-sinh-' . now()->format('d-m-Y') . '.' . $format;

        return Excel::download(
            new StudentExport($classId),
            $filename
        );
    }
}