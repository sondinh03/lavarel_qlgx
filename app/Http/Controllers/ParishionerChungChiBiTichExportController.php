<?php

namespace App\Http\Controllers;

use App\Actions\Parishioner\ExportChungChiBiTichAction;
use App\Models\Parishioner;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ParishionerChungChiBiTichExportController extends Controller
{
    public function __invoke(
        Request $request,
        Parishioner $parishioner,
        ExportChungChiBiTichAction $action
    ): BinaryFileResponse {
        $this->authorize('view', $parishioner);

        $validated = $request->validate([
            'recipient_priest'  => 'nullable|string|max:255',
            'recipient_diocese' => 'nullable|string|max:255',
            'purpose'           => 'nullable|string|max:500',
        ]);

        $result = $action->handle(
            $parishioner,
            (string) ($validated['recipient_priest'] ?? ''),
            (string) ($validated['recipient_diocese'] ?? ''),
            (string) ($validated['purpose'] ?? ''),
        );

        return response()
            ->download($result['path'], $result['filename'])
            ->deleteFileAfterSend(true);
    }
}
