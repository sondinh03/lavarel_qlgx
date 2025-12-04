<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Facades\Excel;


class UsersImportController extends Controller
{
    public function show()
    {
        return view('page.import');
    }
    
    public function store(Request $request)
    {
        $file = $request->file('file')->store('import');
        
        if(empty($file)){
            return back()->withStatus('Bạn cần tiến hành chọn file trước khi import');
        }
        
        $import = new UsersImport;
        $import->import($file);
        
        if ($import->failures()->isNotEmpty()) {
            return back()->withFailures($import->failures());
        }
        
        return back()->withStatus('Đã hoàn thành');
    }
}