<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ParishionerImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ParishionerGiaoDanTemplateSheet(),
            new ParishionerBiTichHonPhoiTemplateSheet(),
        ];
    }
}
