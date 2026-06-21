<?php

namespace App\Http\Controllers;

use App\Exports\ParishionerImportTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ParishionerImportController extends Controller
{
    public function template(): BinaryFileResponse
    {
        return Excel::download(
            new ParishionerImportTemplateExport(),
            'parishioner_import_template.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }
}
