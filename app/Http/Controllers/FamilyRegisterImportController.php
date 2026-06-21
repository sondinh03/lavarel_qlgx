<?php

namespace App\Http\Controllers;

use App\Exports\FamilyRegisterImportTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FamilyRegisterImportController extends Controller
{
    public function template(): BinaryFileResponse
    {
        return Excel::download(
            new FamilyRegisterImportTemplateExport(),
            'template_so_gia_dinh.xlsx',
            \Maatwebsite\Excel\Excel::XLSX
        );
    }
}
