<?php

namespace App\Http\Controllers;

use App\Actions\Family\ExportSoGiaDinhAction;
use App\Models\Family;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FamilySoGiaDinhExportController extends Controller
{
    public function __invoke(Family $family, ExportSoGiaDinhAction $action): BinaryFileResponse
    {
        $this->authorize('view', $family);

        $result = $action->handle($family);

        return response()
            ->download($result['path'], $result['filename'])
            ->deleteFileAfterSend(true);
    }
}
