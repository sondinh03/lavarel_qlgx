<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use App\Exports\UsersExport;
use App\Exports\UserMultiSheetExport;
//use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel;

class UsersExportController extends Controller
{    
    public function store()
    {
        if(empty(is_numeric($_POST['giaophan']))){
            return back()->withErrors('Bạn chưa chọn giáo phận');
        }elseif(empty(is_numeric($_POST['giaohat']))){
            return back()->withErrors('Bạn chưa chọn giáo hạt');
        }elseif(empty(is_numeric($_POST['giaoxu']))){
            return back()->withErrors('Bạn chưa chọn giáo xứ');
        }else{
            return Excel::download(new UserMultiSheetExport(), 'GiaDinh.xlsx');
        }
    }
}