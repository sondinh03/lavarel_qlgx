<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FamilyRegisterImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new FamilyRegisterReadmeSheet(),
            new FamilyRegisterParishionersSheet(),
            new FamilyRegisterSacramentsSheet(),
            new FamilyRegisterMarriagesSheet(),
        ];
    }
}
