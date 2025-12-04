<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HonPhoiExport;

class HonPhoiExportController extends Controller
{
    public function store()
    {       
        return Excel::download(new HonPhoiExport(), 'DanhSachRaoHonPhoi.xlsx');
        /*
        if(empty(is_numeric($_POST['giaophan']))){
            return back()->withErrors('Bạn chưa chọn giáo phận');
        }elseif(empty(is_numeric($_POST['giaohat']))){
            return back()->withErrors('Bạn chưa chọn giáo hạt');
        }elseif(empty(is_numeric($_POST['giaoxu']))){
            return back()->withErrors('Bạn chưa chọn giáo xứ');
        }elseif(empty(is_numeric($_POST['lop']))){
            return back()->withErrors('Bạn chưa chọn lớp');
        }else{
            return Excel::download(new HonPhoiExport(), 'DanhSachRaoHonPhoi.xlsx');
        }
        */
    }
}
