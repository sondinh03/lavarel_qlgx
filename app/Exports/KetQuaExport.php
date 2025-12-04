<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Lop;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use App\Models\ParishManagement;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class KetQuaExport implements FromCollection, WithMultipleSheets
{
    use RegistersEventListeners;
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //$lop = Lop::where('did', '=', $_POST['giaophan'])->where('deid', '=', $_POST['giaohat'])->where('pid', '=', $_POST['giaoxu'])->where('id', $_POST['lop'])->first();
        //return $lop;
    }
    
    
    public function sheets(): array
    {
        $lop = Lop::where('did', '=', $_POST['giaophan'])->where('deid', '=', $_POST['giaohat'])->where('pid', '=', $_POST['giaoxu'])->where('id', $_POST['lop'])->first();
        
        return [
            $lop->name          => new LopExport(),
            'Điểm danh đi học'  => new DiHocExport(),
            'Điểm danh đi lễ'   => new DiLeExport(),
            'Khảo kinh'         => new KhaoKinhExport(),
        ];
    }
}
