<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UserMultiSheetExport implements FromCollection, WithMultipleSheets
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        //
    }

    public function sheets(): array
    {
        return [
            'Giáo Dân'              => new GiaoDanExport(),
            'Gia Đình - Hôn Phối'   => new GiaDinhExport(),
        ];
    }
}