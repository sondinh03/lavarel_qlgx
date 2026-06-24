<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FamilyRegisterImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new FamilyRegisterHouseholdsSheet(),
            new FamilyRegisterMembersSheet(),
        ];
    }
}
