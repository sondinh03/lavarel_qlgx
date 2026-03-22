<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TeacherImportController extends Controller
{
    /**
     * Download file mẫu import giáo lý viên.
     *
     * File mẫu đặt tại: storage/app/public/templates/teacher_import_template.xlsx
     * Đã chạy: php artisan storage:link
     */
    public function template(): BinaryFileResponse
    {
        $path = storage_path('app\public\templates\teacher_import_template.xlsx');

        abort_unless(file_exists($path), 404, 'File mẫu không tồn tại');

        return response()->download(
            $path,
            'teacher_import_template.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}
