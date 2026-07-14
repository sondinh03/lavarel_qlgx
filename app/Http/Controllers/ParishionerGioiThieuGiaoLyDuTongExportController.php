<?php

namespace App\Http\Controllers;

use App\Actions\Parishioner\ExportGioiThieuGiaoLyDuTongAction;
use App\Models\Parishioner;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ParishionerGioiThieuGiaoLyDuTongExportController extends Controller
{
    public function __invoke(
        Request $request,
        Parishioner $parishioner,
        ExportGioiThieuGiaoLyDuTongAction $action
    ): BinaryFileResponse {
        $this->authorize('view', $parishioner);

        $validated = $request->validate([
            'full_name'    => 'required|string|max:200',
            'birthday'     => 'required|date',
            'address'      => 'required|string|max:255',
            'father_name'  => 'required|string|max:200',
            'mother_name'  => 'required|string|max:200',
            'course_place' => 'nullable|string|max:255',
            'greeting_to'  => 'nullable|string|max:255',
        ], [
            'full_name.required'   => 'Vui lòng nhập họ tên người được giới thiệu.',
            'birthday.required'    => 'Vui lòng nhập ngày sinh.',
            'address.required'     => 'Vui lòng nhập địa chỉ.',
            'father_name.required' => 'Vui lòng nhập tên bố.',
            'mother_name.required' => 'Vui lòng nhập tên mẹ.',
        ]);

        $result = $action->handle(
            $parishioner,
            $validated['full_name'],
            $validated['birthday'],
            $validated['address'],
            $validated['father_name'],
            $validated['mother_name'],
            (string) ($validated['course_place'] ?? ''),
            (string) ($validated['greeting_to'] ?? ''),
        );

        return response()
            ->download($result['path'], $result['filename'])
            ->deleteFileAfterSend(true);
    }
}
