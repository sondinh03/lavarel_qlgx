<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GiaDinh;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GiaDinhSheetExport;
use App\Models\Decen;
use Illuminate\Support\Facades\Auth;

class GiaDinhController extends Controller
{
    public function index() {
        $giadinh = GiaDinh::get();
        return view('giadinh',['Gia Đình' => $giadinh]);
    }
    
    public function export()
    {
        $userId = Auth::id();
        $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
        if(!empty($decen) AND $decen->parish == 1){
            return Excel::download(new GiaDinhSheetExport(), 'GiaDinh.xlsx');
        }else{
            return back()->withErrors('Xin lỗi, bạn không có quyền thực hiện điều này');
        }
    }
}
