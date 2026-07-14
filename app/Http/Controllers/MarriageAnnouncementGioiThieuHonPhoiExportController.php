<?php

namespace App\Http\Controllers;

use App\Actions\MarriageAnnouncement\ExportGioiThieuHonPhoiAction;
use App\Models\MarriageAnnouncement;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MarriageAnnouncementGioiThieuHonPhoiExportController extends Controller
{
    public function __invoke(
        Request $request,
        int $id,
        ExportGioiThieuHonPhoiAction $action
    ): BinaryFileResponse {
        $announcement = MarriageAnnouncement::with([
            'parishioners.parishioner.saint',
            'parishioners.parishioner.parish',
            'parishioners.parishioner.parishGroup',
            'parishioners.parishioner.father.saint',
            'parishioners.parishioner.mother.saint',
        ])->findOrFail($id);

        $this->authorize('view', $announcement);

        $validated = $request->validate([
            'subject_side'      => 'required|in:groom,bride',
            'greeting_parish'   => 'nullable|string|max:255',
            'a_holy_name'       => 'required|string|max:200',
            'a_birthday'        => 'required|date',
            'a_honorific'       => 'nullable|string|max:20',
            'a_birth_place'     => 'nullable|string|max:255',
            'a_father_name'     => 'nullable|string|max:200',
            'a_mother_name'     => 'nullable|string|max:200',
            'a_address'         => 'nullable|string|max:255',
            'a_parish_group'    => 'nullable|string|max:255',
            'a_parish'          => 'nullable|string|max:255',
            'b_holy_name'       => 'required|string|max:200',
            'b_birthday'        => 'required|date',
            'b_honorific'       => 'nullable|string|max:20',
            'b_birth_place'     => 'nullable|string|max:255',
            'b_father_name'     => 'nullable|string|max:200',
            'b_mother_name'     => 'nullable|string|max:200',
            'b_address'         => 'nullable|string|max:255',
            'b_parish_group'    => 'nullable|string|max:255',
            'b_parish'          => 'nullable|string|max:255',
        ], [
            'a_holy_name.required' => 'Vui lòng nhập họ tên đương sự.',
            'a_birthday.required'  => 'Vui lòng nhập ngày sinh đương sự.',
            'b_holy_name.required' => 'Vui lòng nhập họ tên người kết bạn.',
            'b_birthday.required'  => 'Vui lòng nhập ngày sinh người kết bạn.',
        ]);

        $result = $action->handle(
            $announcement,
            $validated['subject_side'],
            (string) ($validated['greeting_parish'] ?? ''),
            [
                'a' => [
                    'honorific'    => $validated['a_honorific'] ?? '',
                    'holy_name'    => $validated['a_holy_name'],
                    'birthday'     => $validated['a_birthday'],
                    'birth_place'  => $validated['a_birth_place'] ?? '',
                    'father_name'  => $validated['a_father_name'] ?? '',
                    'mother_name'  => $validated['a_mother_name'] ?? '',
                    'address'      => $validated['a_address'] ?? '',
                    'parish_group' => $validated['a_parish_group'] ?? '',
                    'parish'       => $validated['a_parish'] ?? '',
                ],
                'b' => [
                    'honorific'    => $validated['b_honorific'] ?? '',
                    'holy_name'    => $validated['b_holy_name'],
                    'birthday'     => $validated['b_birthday'],
                    'birth_place'  => $validated['b_birth_place'] ?? '',
                    'father_name'  => $validated['b_father_name'] ?? '',
                    'mother_name'  => $validated['b_mother_name'] ?? '',
                    'address'      => $validated['b_address'] ?? '',
                    'parish_group' => $validated['b_parish_group'] ?? '',
                    'parish'       => $validated['b_parish'] ?? '',
                ],
            ],
        );

        return response()
            ->download($result['path'], $result['filename'])
            ->deleteFileAfterSend(true);
    }
}
