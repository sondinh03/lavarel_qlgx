<?php

namespace App\Actions\Teacher;

use App\Imports\TeacherImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportTeacherAction
{
    public function handle($file, int $parishId): void
    {
        Excel::import(
            new TeacherImport(
                parishId: $parishId,
            ),
            $file
        );
    }
}
