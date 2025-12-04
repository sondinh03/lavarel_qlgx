<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KetQuaExport;
use App\Models\Lop;

class KetQuaExportController extends Controller
{
    public function store()
    {
        $lop = Lop::where('did', '=', $_POST['giaophan'])->where('deid', '=', $_POST['giaohat'])->where('pid', '=', $_POST['giaoxu'])->where('id', $_POST['lop'])->first();

        $tenlop =  $lop->name . ' - ' . $lop->schoolyear;
        
        if(empty(is_numeric($_POST['giaophan']))){
            return back()->withErrors('Bạn chưa chọn giáo phận');
        }elseif(empty(is_numeric($_POST['giaohat']))){
            return back()->withErrors('Bạn chưa chọn giáo hạt');
        }elseif(empty(is_numeric($_POST['giaoxu']))){
            return back()->withErrors('Bạn chưa chọn giáo xứ');
        }elseif(empty(is_numeric($_POST['lop']))){
            return back()->withErrors('Bạn chưa chọn lớp');
        }else{
            return Excel::download(new KetQuaExport(), $tenlop . '.xlsx');
        }
    }
}
