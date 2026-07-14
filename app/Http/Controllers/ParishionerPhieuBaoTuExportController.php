<?php

namespace App\Http\Controllers;

use App\Actions\Parishioner\ExportPhieuBaoTuAction;
use App\Models\Parishioner;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ParishionerPhieuBaoTuExportController extends Controller
{
    public function __invoke(
        Parishioner $parishioner,
        ExportPhieuBaoTuAction $action
    ): BinaryFileResponse {
        $this->authorize('view', $parishioner);

        abort_unless(
            $parishioner->death_date !== null,
            422,
            'Giáo dân chưa có ngày mất — không thể xuất giấy báo tử.'
        );

        $result = $action->handle($parishioner);

        return response()
            ->download($result['path'], $result['filename'])
            ->deleteFileAfterSend(true);
    }
}
