<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentImportController extends Controller
{
    /**
     * Download file mẫu import học sinh.
     *
     * File mẫu đặt tại: storage/app/public/templates/student_import_template.xlsx
     * Đã chạy: php artisan storage:link
     */
    public function template(): BinaryFileResponse
    {
        $path = storage_path('app\public\templates\student_import_template.xlsx');

        abort_unless(file_exists($path), 404, 'File mẫu không tồn tại');

        return response()->download(
            $path,
            'student_import_template.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}
