<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FamilyRegisterPreviewImport implements ToCollection, WithHeadingRow
{
    public function headingRow(): int
    {
        return 5;
    }

    public function collection(Collection $rows): Collection
    {
        return $rows;
    }
}
