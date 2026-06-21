<?php

namespace App\Http\Controllers;

use App\Actions\Parishioner\ExportLyLichCaNhanAction;
use App\Models\Parishioner;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ParishionerLyLichExportController extends Controller
{
    public function __invoke(Parishioner $parishioner, ExportLyLichCaNhanAction $action): BinaryFileResponse
    {
        $this->authorize('view', $parishioner);

        $result = $action->handle($parishioner);

        return response()->download($result['path'], $result['filename'])
            ->deleteFileAfterSend(true);
    }
}
